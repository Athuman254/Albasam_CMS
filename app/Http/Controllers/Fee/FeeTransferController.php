<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\FeeTransfer;
use App\Models\Student;
use App\Models\Fee;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FeeTransferController extends Controller
{
    public function create()
    {
        $validReasons = config('fees.valid_transfer_reasons', []);
        
        return Inertia::render('Admin/Fees/TransferFunds', [
            'validReasons' => $validReasons,
        ]);
    }

    public function store(Request $request)
    {
        $validReasons = array_keys(config('fees.valid_transfer_reasons', []));
        
        $validated = $request->validate([
            'from_admission_number' => 'required|string|exists:students,admission_number',
            'to_admission_number' => 'required|string|exists:students,admission_number|different:from_admission_number',
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'reason_type' => 'required|string|in:' . implode(',', $validReasons),
            'reason_notes' => 'nullable|string|max:1000',
        ], [
            'to_admission_number.different' => 'Cannot transfer funds to the same student.',
            'amount.regex' => 'Amount must have at most 2 decimal places.',
            'reason_type.required' => 'Please select a valid transfer reason.',
            'reason_type.in' => 'Selected reason is not valid.',
        ]);

        // Additional validation for "other" reason
        if ($validated['reason_type'] === 'other' && empty($validated['reason_notes'])) {
            return response()->json([
                'success' => false,
                'message' => 'Additional notes are required for "Other" transfer reason.',
                'errors' => ['reason_notes' => ['Additional notes are required for "Other" transfer reason.']]
            ], 422);
        }

        try {
            DB::transaction(function () use ($validated) {
                // Find students with lock to prevent race conditions
                $fromStudent = Student::where('admission_number', $validated['from_admission_number'])
                    ->lockForUpdate()
                    ->firstOrFail();
                    
                $toStudent = Student::where('admission_number', $validated['to_admission_number'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // Validate transfer amount and check for overdue fees
                $this->validateTransfer($fromStudent, $validated['amount']);

                // Check if reason requires approval
                $requireApproval = config('fees.require_reason_approval', []);
                $needsApproval = isset($requireApproval[$validated['reason_type']]) && 
                               $requireApproval[$validated['reason_type']];

                // Create transfer record
                $transferData = [
                    'from_student_id' => $fromStudent->id,
                    'to_student_id' => $toStudent->id,
                    'amount' => $validated['amount'],
                    'reason_type' => $validated['reason_type'],
                    'reason_notes' => $validated['reason_notes'] ?? null,
                    'initiated_by' => auth()->id(),
                    'status' => $needsApproval ? 'pending_approval' : 'completed',
                    'transfer_date' => now(),
                ];

                $transfer = FeeTransfer::create($transferData);

                // If no approval needed, process immediately
                if (!$needsApproval) {
                    $this->processTransfer($transfer, $fromStudent, $toStudent);
                    
                    // Log the successful transfer
                    Log::info('Fee transfer completed', [
                        'transfer_id' => $transfer->id,
                        'from_student' => $fromStudent->admission_number,
                        'to_student' => $toStudent->admission_number,
                        'amount' => $validated['amount'],
                        'reason' => $validated['reason_type'],
                        'initiated_by' => auth()->id()
                    ]);
                } else {
                    // Log pending approval
                    Log::info('Fee transfer pending approval', [
                        'transfer_id' => $transfer->id,
                        'reason' => $validated['reason_type'],
                        'initiated_by' => auth()->id()
                    ]);
                }
            });

            $message = 'Funds transferred successfully!';
            $requiresApproval = false;
            
            $requireApproval = config('fees.require_reason_approval', []);
            if (isset($requireApproval[$validated['reason_type']]) && $requireApproval[$validated['reason_type']]) {
                $message = 'Transfer submitted for approval. Please wait for authorization.';
                $requiresApproval = true;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'requires_approval' => $requiresApproval
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Fee transfer failed: ' . $e->getMessage(), [
                'from_admission' => $validated['from_admission_number'] ?? 'unknown',
                'to_admission' => $validated['to_admission_number'] ?? 'unknown',
                'amount' => $validated['amount'] ?? 0,
                'reason' => $validated['reason_type'] ?? 'unknown',
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error transferring funds: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate transfer with proper overdue fee checking
     */
    private function validateTransfer(Student $fromStudent, float $amount): void
    {
        // Get available balance (positive balances only)
        $availableBalance = Fee::where('student_id', $fromStudent->id)
            ->where('balance', '>', 0)
            ->sum('balance');

        if ($amount > $availableBalance) {
            throw new \Exception(
                'Insufficient balance. Available balance: KSh ' . 
                number_format($availableBalance, 2) . 
                ' | Requested: KSh ' . number_format($amount, 2)
            );
        }

        // Check if student has overdue fees - ALLOW transfers only if student has overdue fees
        $hasOverdueFees = Fee::where('student_id', $fromStudent->id)
            ->where('balance', '>', 0)
            ->where('due_date', '<', now())
            ->exists();

        if (!$hasOverdueFees) {
            throw new \Exception(
                'The student has no overdue fees for transfer. Transfers are only allowed for students with overdue fees.'
            );
        }

        // Check minimum transfer amount
        $minTransfer = config('fees.min_transfer_amount', 1);
        if ($amount < $minTransfer) {
            throw new \Exception('Minimum transfer amount is KSh ' . number_format($minTransfer, 2));
        }

        // Check maximum transfer amount
        $maxTransfer = config('fees.max_transfer_amount', 100000);
        if ($amount > $maxTransfer) {
            throw new \Exception('Maximum transfer amount is KSh ' . number_format($maxTransfer, 2));
        }

        // Check if student has any pending transfers
        $pendingTransfer = FeeTransfer::where('from_student_id', $fromStudent->id)
            ->whereIn('status', ['pending_approval', 'processing'])
            ->exists();

        if ($pendingTransfer) {
            throw new \Exception('Student has pending transfers. Please wait for them to be processed.');
        }

        // Check if transfer amount exceeds overdue amount
        $overdueAmount = Fee::where('student_id', $fromStudent->id)
            ->where('balance', '>', 0)
            ->where('due_date', '<', now())
            ->sum('balance');

        if ($amount > $overdueAmount) {
            throw new \Exception(
                'Transfer amount cannot exceed overdue amount. Overdue amount: KSh ' . 
                number_format($overdueAmount, 2)
            );
        }
    }

    /**
     * Process the actual fund transfer between students
     */
    private function processTransfer(FeeTransfer $transfer, Student $fromStudent, Student $toStudent): void
    {
        $remainingAmount = $transfer->amount;

        // Get overdue fees with positive balance for the sender (oldest first)
        $fromFees = Fee::where('student_id', $fromStudent->id)
            ->where('balance', '>', 0)
            ->where('due_date', '<', now()) // Only overdue fees
            ->orderBy('due_date')
            ->lockForUpdate()
            ->get();

        if ($fromFees->isEmpty()) {
            throw new \Exception('No overdue fees found for transfer.');
        }

        // Process deduction from sender's overdue fees
        foreach ($fromFees as $fee) {
            if ($remainingAmount <= 0) break;

            $deductAmount = min($fee->balance, $remainingAmount);

            // Create transfer-out payment record (negative amount for deduction)
            $transferOutPayment = FeePayment::create([
                'fee_id' => $fee->id,
                'student_id' => $fromStudent->id,
                'amount' => -$deductAmount, // Negative amount for deduction
                'payment_method' => 'transfer_out',
                'reference_number' => 'TRANSFER-OUT-' . $transfer->id,
                'payment_date' => now(),
                'status' => 'completed',
                'notes' => sprintf(
                    'Transfer to %s (%s) - %s | Reason: %s',
                    $toStudent->full_name,
                    $toStudent->admission_number,
                    $transfer->formatted_reason,
                    $transfer->reason_notes ?: 'No additional notes'
                ),
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'transfer_id' => $transfer->id,
                'transaction_type' => 'transfer_out'
            ]);

            // Update fee balance for sender
            $fee->paid_amount = max(0, $fee->paid_amount - $deductAmount);
            $fee->balance = $fee->amount - $fee->paid_amount;
            $fee->status = $this->calculateFeeStatus($fee);
            
            // Record transfer transaction in fee description
            $this->recordFeeTransferTransaction($fee, -$deductAmount, 'transfer_out', $transfer);
            
            $fee->save();

            $remainingAmount -= $deductAmount;

            Log::info('Transfer deduction processed from overdue fee', [
                'fee_id' => $fee->id,
                'student_id' => $fromStudent->id,
                'deduct_amount' => $deductAmount,
                'remaining_balance' => $fee->balance,
                'due_date' => $fee->due_date,
                'transfer_id' => $transfer->id
            ]);
        }

        if ($remainingAmount > 0) {
            throw new \Exception('Insufficient overdue balance during transfer processing. Remaining: ' . $remainingAmount);
        }

        // Allocate funds to recipient
        $this->allocateToRecipient($transfer, $toStudent);

        // Update transfer status to completed
        $transfer->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        Log::info('Transfer completed successfully', [
            'transfer_id' => $transfer->id,
            'from_student' => $fromStudent->admission_number,
            'to_student' => $toStudent->admission_number,
            'amount' => $transfer->amount,
            'from_overdue_fees' => true
        ]);
    }

    /**
     * Record transfer transaction in fee table for audit trail
     */
    private function recordFeeTransferTransaction(Fee $fee, float $amount, string $type, FeeTransfer $transfer): void
    {
        $transactionNote = sprintf(
            '%s: %s KSh %s for transfer %s - %s',
            $type === 'transfer_out' ? 'Transfer Out' : 'Transfer In',
            $type === 'transfer_out' ? 'to' : 'from',
            number_format(abs($amount), 2),
            $transfer->id,
            $transfer->formatted_reason
        );

        // Update fee description to include transfer history
        $currentDescription = $fee->description ?: '';
        $newDescription = $currentDescription . "\n" . $transactionNote . ' [' . now()->format('Y-m-d H:i:s') . ']';
        
        // Limit description length to avoid database issues
        if (strlen($newDescription) > 1000) {
            $newDescription = substr($newDescription, 0, 1000) . '... [truncated]';
        }
        
        $fee->description = $newDescription;
    }

    /**
     * Allocate transferred funds to recipient student
     */
    private function allocateToRecipient(FeeTransfer $transfer, Student $toStudent): void
    {
        $allocatedAmount = 0;

        // First, try to allocate to existing unpaid fees (prioritize overdue fees first)
        $unpaidFees = Fee::where('student_id', $toStudent->id)
            ->where('balance', '>', 0)
            ->orderByRaw('due_date < NOW() DESC') // Overdue fees first
            ->orderBy('due_date') // Then by due date
            ->lockForUpdate()
            ->get();

        foreach ($unpaidFees as $fee) {
            if ($allocatedAmount >= $transfer->amount) break;

            $allocateAmount = min($fee->balance, $transfer->amount - $allocatedAmount);

            // Create transfer-in payment record
            $transferInPayment = FeePayment::create([
                'fee_id' => $fee->id,
                'student_id' => $toStudent->id,
                'amount' => $allocateAmount,
                'payment_method' => 'transfer_in',
                'reference_number' => 'TRANSFER-IN-' . $transfer->id,
                'payment_date' => now(),
                'status' => 'completed',
                'notes' => sprintf(
                    'Transfer from %s (%s) - %s | Reason: %s',
                    $transfer->fromStudent->full_name,
                    $transfer->fromStudent->admission_number,
                    $transfer->formatted_reason,
                    $transfer->reason_notes ?: 'No additional notes'
                ),
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'transfer_id' => $transfer->id,
                'transaction_type' => 'transfer_in'
            ]);

            // Update fee balance for recipient
            $fee->paid_amount += $allocateAmount;
            $fee->balance = max(0, $fee->amount - $fee->paid_amount);
            $fee->status = $this->calculateFeeStatus($fee);
            
            // Record the transfer transaction
            $this->recordFeeTransferTransaction($fee, $allocateAmount, 'transfer_in', $transfer);
            
            $fee->save();

            $allocatedAmount += $allocateAmount;

            Log::info('Transfer allocation to recipient fee', [
                'fee_id' => $fee->id,
                'student_id' => $toStudent->id,
                'allocated_amount' => $allocateAmount,
                'remaining_balance' => $fee->balance,
                'due_date' => $fee->due_date,
                'transfer_id' => $transfer->id
            ]);
        }

        // If there's remaining amount after paying existing fees, create a credit entry
        if ($allocatedAmount < $transfer->amount) {
            $remainingCredit = $transfer->amount - $allocatedAmount;
            
            $creditFee = Fee::create([
                'student_id' => $toStudent->id,
                'rank_id' => $toStudent->current_rank_id,
                'fee_type' => 'transfer_credit',
                'amount' => $remainingCredit,
                'paid_amount' => $remainingCredit,
                'balance' => 0,
                'academic_year' => now()->year,
                'term' => $this->getCurrentTerm(),
                'due_date' => now()->addYear(), // Credit valid for 1 year
                'status' => 'paid',
                'description' => sprintf(
                    'Transfer credit from %s (%s) - %s | Reason: %s | Transfer ID: %s',
                    $transfer->fromStudent->full_name,
                    $transfer->fromStudent->admission_number,
                    $transfer->formatted_reason,
                    $transfer->reason_notes ?: 'No additional notes',
                    $transfer->id
                ),
                'is_transfer_credit' => true,
                'transfer_id' => $transfer->id,
            ]);

            // Create payment record for the credit
            FeePayment::create([
                'fee_id' => $creditFee->id,
                'student_id' => $toStudent->id,
                'amount' => $remainingCredit,
                'payment_method' => 'transfer_in',
                'reference_number' => 'TRANSFER-CREDIT-' . $transfer->id,
                'payment_date' => now(),
                'status' => 'completed',
                'notes' => sprintf(
                    'Transfer credit from %s (%s) - %s',
                    $transfer->fromStudent->full_name,
                    $transfer->fromStudent->admission_number,
                    $transfer->formatted_reason
                ),
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'transfer_id' => $transfer->id,
                'transaction_type' => 'transfer_credit'
            ]);

            Log::info('Transfer credit created for recipient', [
                'credit_fee_id' => $creditFee->id,
                'student_id' => $toStudent->id,
                'credit_amount' => $remainingCredit,
                'transfer_id' => $transfer->id
            ]);
        }
    }

    /**
     * Get current academic term
     */
    private function getCurrentTerm(): string
    {
        $month = now()->month;
        
        if ($month >= 1 && $month <= 4) return '1';
        if ($month >= 5 && $month <= 8) return '2';
        return '3';
    }

    /**
     * Calculate fee status based on payment
     */
    private function calculateFeeStatus(Fee $fee): string
    {
        if ($fee->balance <= 0) {
            return 'paid';
        } elseif ($fee->paid_amount > 0) {
            return 'partial';
        } elseif ($fee->due_date && $fee->due_date->lt(now())) {
            return 'overdue';
        } else {
            return 'pending';
        }
    }

    /**
     * Get student's overdue fee details
     */
    public function getStudentOverdueDetails(Request $request, $studentId)
    {
        try {
            $student = Student::findOrFail($studentId);
            
            $overdueFees = Fee::where('student_id', $student->id)
                ->where('balance', '>', 0)
                ->where('due_date', '<', now())
                ->orderBy('due_date')
                ->get();

            $totalOverdue = $overdueFees->sum('balance');
            $overdueCount = $overdueFees->count();

            return response()->json([
                'success' => true,
                'overdue_fees' => $overdueFees,
                'total_overdue' => $totalOverdue,
                'overdue_count' => $overdueCount,
                'has_overdue' => $overdueCount > 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching overdue details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transfer statistics - FIXED ROUTE
     */
    public function stats(Request $request)
    {
        try {
            $today = now()->format('Y-m-d');
            $monthStart = now()->startOfMonth()->format('Y-m-d');

            $stats = [
                'today_transfers' => FeeTransfer::whereDate('created_at', $today)
                    ->where('status', 'completed')
                    ->count(),
                'today_amount' => FeeTransfer::whereDate('created_at', $today)
                    ->where('status', 'completed')
                    ->sum('amount'),
                'month_transfers' => FeeTransfer::whereDate('created_at', '>=', $monthStart)
                    ->where('status', 'completed')
                    ->count(),
                'total_amount' => FeeTransfer::where('status', 'completed')
                    ->sum('amount'),
                'pending_approvals' => FeeTransfer::where('status', 'pending_approval')->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error fetching transfer stats: ' . $e->getMessage());
            return response()->json([
                'today_transfers' => 0,
                'today_amount' => 0,
                'month_transfers' => 0,
                'total_amount' => 0,
                'pending_approvals' => 0,
            ]);
        }
    }

    /**
     * Get valid transfer reasons - FIXED ROUTE
     */
    public function reasons(Request $request)
    {
        try {
            $validReasons = config('fees.valid_transfer_reasons', []);
            $requireApproval = config('fees.require_reason_approval', []);

            $reasonsWithApproval = [];
            foreach ($validReasons as $key => $label) {
                $reasonsWithApproval[$key] = [
                    'label' => $label,
                    'requires_approval' => isset($requireApproval[$key]) && $requireApproval[$key]
                ];
            }

            return response()->json($reasonsWithApproval);
        } catch (\Exception $e) {
            Log::error('Error fetching transfer reasons: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Get pending approvals - FIXED ROUTE
     */
    public function pendingApprovals(Request $request)
    {
        try {
            $transfers = FeeTransfer::with(['fromStudent', 'toStudent', 'initiatedBy'])
                ->where('status', 'pending_approval')
                ->latest()
                ->paginate(20);

            return Inertia::render('Admin/Fees/TransferApprovals', [
                'transfers' => $transfers,
                'filters' => $request->only(['search']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pending approvals: ' . $e->getMessage());
            return Inertia::render('Admin/Fees/TransferApprovals', [
                'transfers' => [],
                'filters' => $request->only(['search']),
            ]);
        }
    }

    /**
     * Approve a pending transfer
     */
    public function approve(Request $request, FeeTransfer $transfer)
    {
        if ($transfer->status !== 'pending_approval') {
            return response()->json([
                'success' => false,
                'message' => 'This transfer does not require approval or has already been processed.'
            ], 422);
        }

        try {
            DB::transaction(function () use ($transfer) {
                $fromStudent = $transfer->fromStudent;
                $toStudent = $transfer->toStudent;

                // Re-validate before processing (check if still has overdue fees)
                $this->validateTransfer($fromStudent, $transfer->amount);

                // Process the transfer
                $this->processTransfer($transfer, $fromStudent, $toStudent);

                // Mark as approved
                $transfer->update([
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'status' => 'completed',
                ]);

                Log::info('Fee transfer approved and processed', [
                    'transfer_id' => $transfer->id,
                    'approved_by' => auth()->id()
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Transfer approved and processed successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Fee transfer approval failed: ' . $e->getMessage(), [
                'transfer_id' => $transfer->id,
                'approved_by' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error approving transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a pending transfer
     */
    public function reject(Request $request, FeeTransfer $transfer)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        if ($transfer->status !== 'pending_approval') {
            return response()->json([
                'success' => false,
                'message' => 'This transfer cannot be rejected.'
            ], 422);
        }

        try {
            $transfer->update([
                'status' => 'rejected',
                'reason_notes' => $transfer->reason_notes . ' [REJECTED: ' . $request->rejection_reason . ']',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            Log::info('Fee transfer rejected', [
                'transfer_id' => $transfer->id,
                'rejected_by' => auth()->id(),
                'reason' => $request->rejection_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer rejected successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Fee transfer rejection failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error rejecting transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $validReasons = config('fees.valid_transfer_reasons', []);
        $transfers = FeeTransfer::with(['fromStudent', 'toStudent', 'initiatedBy', 'approvedBy'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Fees/TransferHistory', [
            'transfers' => $transfers,
            'validReasons' => $validReasons,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(FeeTransfer $transfer)
    {
        $transfer->load([
            'fromStudent', 
            'toStudent', 
            'initiatedBy',
            'approvedBy',
            'feePayments.fee'
        ]);

        return Inertia::render('Admin/Fees/TransferDetails', [
            'transfer' => $transfer,
        ]);
    }

    public function dataTable(Request $request)
    {
        $transfers = FeeTransfer::with(['fromStudent', 'toStudent', 'initiatedBy', 'approvedBy'])
            ->when($request->has('search') && $request->search, function($query) use ($request) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('fromStudent', function($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%")
                          ->orWhere('admission_number', 'like', "%{$search}%");
                    })->orWhereHas('toStudent', function($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%")
                          ->orWhere('admission_number', 'like', "%{$search}%");
                    })->orWhere('reason_type', 'like', "%{$search}%")
                      ->orWhere('reference_number', 'like', "%{$search}%");
                });
            })
            ->when($request->has('date_from') && $request->date_from, function($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->has('date_to') && $request->date_to, function($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->has('status') && $request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->has('reason_type') && $request->reason_type, function($query) use ($request) {
                $query->where('reason_type', $request->reason_type);
            })
            ->latest();

        return datatables()->eloquent($transfers)
            ->addColumn('from_student_name', function($transfer) {
                return $transfer->fromStudent->full_name . ' (' . $transfer->fromStudent->admission_number . ')';
            })
            ->addColumn('to_student_name', function($transfer) {
                return $transfer->toStudent->full_name . ' (' . $transfer->toStudent->admission_number . ')';
            })
            ->addColumn('initiated_by_name', function($transfer) {
                return $transfer->initiatedBy->name;
            })
            ->addColumn('approved_by_name', function($transfer) {
                return $transfer->approvedBy ? $transfer->approvedBy->name : '-';
            })
            ->addColumn('formatted_reason', function($transfer) {
                return $transfer->formatted_reason;
            })
            ->addColumn('formatted_amount', function($transfer) {
                return 'KSh ' . number_format($transfer->amount, 2);
            })
            ->addColumn('formatted_date', function($transfer) {
                return $transfer->created_at->format('M j, Y g:i A');
            })
            ->addColumn('status_badge', function($transfer) {
                $statusClass = [
                    'completed' => 'bg-success',
                    'failed' => 'bg-danger',
                    'pending_approval' => 'bg-warning',
                    'rejected' => 'bg-secondary',
                ][$transfer->status] ?? 'bg-secondary';
                
                return '<span class="badge ' . $statusClass . '">' . ucfirst(str_replace('_', ' ', $transfer->status)) . '</span>';
            })
            ->addColumn('actions', function($transfer) {
                $actions = '
                    <button class="btn btn-sm btn-info view-transfer" 
                            data-id="'.$transfer->id.'"
                            data-bs-toggle="tooltip" 
                            title="View Transfer Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <a href="'.route('admin.fees.transfers.show', $transfer->id).'" 
                       class="btn btn-sm btn-outline-primary ms-1"
                       data-bs-toggle="tooltip" 
                       title="View Full Details">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                ';

                // Add approve/reject buttons for pending approvals
                if ($transfer->status === 'pending_approval' && auth()->user()->can('approve_transfers')) {
                    $actions .= '
                        <button class="btn btn-sm btn-success approve-transfer ms-1" 
                                data-id="'.$transfer->id.'"
                                data-bs-toggle="tooltip" 
                                title="Approve Transfer">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger reject-transfer ms-1" 
                                data-id="'.$transfer->id.'"
                                data-bs-toggle="tooltip" 
                                title="Reject Transfer">
                            <i class="fas fa-times"></i>
                        </button>
                    ';
                }

                return $actions;
            })
            ->rawColumns(['actions', 'status_badge'])
            ->toJson();
    }
}