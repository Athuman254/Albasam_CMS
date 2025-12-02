<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\AutoRecordedPayment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class FeePaymentController extends Controller
{
    private $createdPayment;

    public function verify()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return Inertia::render('Admin/Fees/VerifyPayment');
    }

    public function checkPayment(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        $request->validate([
            'reference_number' => 'required|string',
            'admission_number' => 'required|string'
        ]);

        $autoPayment = AutoRecordedPayment::where('reference_number', $request->reference_number)
            ->whereIn('status', ['recorded', 'unmatched'])
            ->first();

        if (!$autoPayment) {
            return response()->json([
                'success' => false,
                'message' => 'No payment found with this reference number. Please check the reference or wait for automatic recording.'
            ]);
        }

        $student = Student::where('admission_number', $request->admission_number)
            ->with(['currentRank', 'fees' => function ($query) {
                $query->where('balance', '>', 0)
                    ->where('status', '!=', 'paid')
                    ->where('status', '!=', 'carried_over')
                    ->orderBy('academic_year', 'desc')
                    ->orderBy('term', 'desc');
            }])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found with the provided admission number.'
            ]);
        }

        $existingPayment = FeePayment::where('reference_number', $request->reference_number)
            ->where('status', 'completed')
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment with this reference number already exists and has been processed.'
            ]);
        }

        // Calculate total outstanding balance including all fee types
        $totalOutstandingBalance = $student->fees->sum('balance');

        // Get fee breakdown by type
        $feeBreakdown = $student->fees->groupBy('fee_type')->map(function ($fees, $type) {
            return [
                'total_amount' => $fees->sum('amount'),
                'total_paid' => $fees->sum('paid_amount'),
                'total_balance' => $fees->sum('balance'),
                'fees' => $fees
            ];
        });

        return response()->json([
            'success' => true,
            'auto_payment' => $autoPayment,
            'student' => $student,
            'outstanding_fees' => $student->fees,
            'total_outstanding_balance' => $totalOutstandingBalance,
            'fee_breakdown' => $feeBreakdown
        ]);
    }

    /**
     * Get outstanding fees for a student (for frontend)
     */
    public function getOutstandingFees(Student $student)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        try {
            $outstandingFees = Fee::where('student_id', $student->id)
                ->where('balance', '>', 0)
                ->where('status', '!=', 'paid')
                ->orderBy('academic_year', 'desc')
                ->orderBy('term', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'fees' => $outstandingFees,
                'total_outstanding' => $outstandingFees->sum('balance')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching outstanding fees: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all outstanding fees for student (including other fees not initially selected)
     */
    public function getAllOutstandingFees(Student $student)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        try {
            $outstandingFees = Fee::where('student_id', $student->id)
                ->where('balance', '>', 0)
                ->where('status', '!=', 'paid')
                ->where('status', '!=', 'carried_over')
                ->orderBy('academic_year', 'desc')
                ->orderBy('term', 'desc')
                ->orderBy('due_date', 'asc') // Overdue fees first
                ->get();

            return response()->json([
                'success' => true,
                'fees' => $outstandingFees,
                'total_outstanding' => $outstandingFees->sum('balance')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching outstanding fees: ' . $e->getMessage()
            ]);
        }
    }

    public function confirmPayment(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        $request->validate([
            'auto_payment_id' => 'required|exists:auto_recorded_payments,id',
            'student_id' => 'required|exists:students,id',
            'fee_ids' => 'required|array',
            'fee_ids.*' => 'exists:fees,id',
            'distribution_type' => 'required|in:auto_distribute,accept_overpayment',
            'notes' => 'sometimes|string'
        ]);

        try {
            DB::transaction(function () use ($request) {

                $autoPayment = AutoRecordedPayment::findOrFail($request->auto_payment_id);
                $student = Student::findOrFail($request->student_id);
                $selectedFeeIds = $request->fee_ids;

                if ($autoPayment->matched_student_id && $autoPayment->matched_student_id != $student->id) {
                    throw new \Exception('Payment appears to belong to a different student. Please verify.');
                }

                $paymentAmount = $autoPayment->amount;
                $distributionType = $request->distribution_type;

                // Get all selected fees
                $selectedFees = Fee::whereIn('id', $selectedFeeIds)->get();

                // Calculate total balance of selected fees
                $totalSelectedBalance = $selectedFees->sum('balance');

                // Handle different distribution types
                switch ($distributionType) {
                    case 'auto_distribute':
                        $this->autoDistributePayment($student, $autoPayment, $selectedFees, $paymentAmount);
                        break;

                    case 'accept_overpayment':
                        $this->applyPaymentWithOverpayment($student, $autoPayment, $selectedFees, $paymentAmount);
                        break;
                }

                // Update auto payment record
                $autoPayment->update([
                    'status' => 'verified',
                    'matched_student_id' => $student->id,
                    'matched_admission_number' => $student->admission_number,
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                    'verification_notes' => $request->notes ?? 'Payment verified and distributed',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and applied successfully!',
                'payment_id' => $this->createdPayment->id,
                'receipt_url' => route('admin.fees.payments.receipt', $this->createdPayment->id),
                'receipt_download_url' => route('admin.fees.payments.receipt.download', $this->createdPayment->id)
            ]);
        } catch (\Exception $e) {
            Log::error('Payment confirmation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error confirming payment: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Legacy method for single fee payment (backward compatibility)
     */
    public function confirmSinglePayment(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        $request->validate([
            'auto_payment_id' => 'required|exists:auto_recorded_payments,id',
            'student_id' => 'required|exists:students,id',
            'fee_id' => 'required|exists:fees,id',
            'apply_to_other_fees' => 'sometimes|boolean',
            'accept_overpayment' => 'sometimes|boolean'
        ]);

        try {
            DB::transaction(function () use ($request) {

                $autoPayment = AutoRecordedPayment::findOrFail($request->auto_payment_id);
                $student = Student::findOrFail($request->student_id);
                $selectedFee = Fee::findOrFail($request->fee_id);

                if ($autoPayment->matched_student_id && $autoPayment->matched_student_id != $student->id) {
                    throw new \Exception('Payment appears to belong to a different student. Please verify.');
                }

                $paymentAmount = $autoPayment->amount;
                $remainingAmount = $paymentAmount;

                // Check if payment exceeds selected fee balance
                if ($paymentAmount > $selectedFee->balance) {
                    $excessAmount = $paymentAmount - $selectedFee->balance;

                    // Get other outstanding fees
                    $otherOutstandingFees = Fee::where('student_id', $student->id)
                        ->where('id', '!=', $selectedFee->id)
                        ->where('balance', '>', 0)
                        ->where('status', '!=', 'paid')
                        ->where('status', '!=', 'carried_over')
                        ->orderBy('academic_year', 'desc')
                        ->orderBy('term', 'desc')
                        ->get();

                    $totalOtherOutstanding = $otherOutstandingFees->sum('balance');
                    $totalOutstandingBalance = $selectedFee->balance + $totalOtherOutstanding;

                    // If apply_to_other_fees is true, check other outstanding fees
                    if ($request->get('apply_to_other_fees', false)) {

                        if ($excessAmount <= $totalOtherOutstanding) {
                            // Apply excess to other fees
                            $this->applyPaymentToMultipleFees(
                                $student,
                                $autoPayment,
                                $selectedFee,
                                $paymentAmount,
                                $otherOutstandingFees
                            );
                            return;
                        } else {
                            // Still have excess after applying to all fees
                            $remainingExcess = $excessAmount - $totalOtherOutstanding;

                            if ($request->get('accept_overpayment', false)) {
                                // Accept overpayment and create credit
                                $this->applyPaymentWithOverpaymentLegacy(
                                    $student,
                                    $autoPayment,
                                    $selectedFee,
                                    $paymentAmount,
                                    $otherOutstandingFees,
                                    $remainingExcess
                                );
                                return;
                            } else {
                                throw new \Exception(
                                    'Payment amount (KSh ' . number_format($paymentAmount, 2) .
                                        ') exceeds total outstanding balance of KSh ' .
                                        number_format($totalOutstandingBalance, 2) .
                                        '. You can accept overpayment to create a credit balance of KSh ' .
                                        number_format($remainingExcess, 2) . '.'
                                );
                            }
                        }
                    } else if ($request->get('accept_overpayment', false)) {
                        // Accept overpayment for selected fee only
                        $this->applyOverpaymentToSingleFee($student, $autoPayment, $selectedFee, $paymentAmount);
                        return;
                    } else {
                        // Provide helpful error message with options
                        $message = 'Payment amount (KSh ' . number_format($paymentAmount, 2) .
                            ') exceeds selected fee balance of KSh ' .
                            number_format($selectedFee->balance, 2);

                        if ($totalOtherOutstanding > 0) {
                            $message .= '. There are other outstanding fees totaling KSh ' .
                                number_format($totalOtherOutstanding, 2) .
                                ' that you can apply the excess to.';
                        } else {
                            $message .= '. You can accept overpayment to create a credit balance.';
                        }

                        throw new \Exception($message);
                    }
                }

                // Normal case - payment within selected fee balance
                $this->applyPaymentToSingleFee($student, $autoPayment, $selectedFee, $paymentAmount);
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and applied successfully!',
                'payment_id' => $this->createdPayment->id,
                'receipt_url' => route('admin.fees.payments.receipt', $this->createdPayment->id),
                'receipt_download_url' => route('admin.fees.payments.receipt.download', $this->createdPayment->id)
            ]);
        } catch (\Exception $e) {
            Log::error('Payment confirmation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error confirming payment: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Automatically distribute payment across all selected fees
     */
    private function autoDistributePayment($student, $autoPayment, $selectedFees, $totalAmount)
    {
        $remainingAmount = $totalAmount;
        $appliedFees = [];
        $paymentReference = $autoPayment->reference_number;

        // Sort fees by priority (overdue first, then by due date, then by amount)
        $sortedFees = $selectedFees->sortBy(function ($fee) {
            $priority = 0;
            if ($fee->due_date && $fee->due_date < now()) {
                $priority += 1000; // High priority for overdue fees
            }
            // Add priority for earlier due dates
            if ($fee->due_date) {
                $priority += (100 - (int)$fee->due_date->diffInDays(now()));
            }
            return $priority;
        });

        foreach ($sortedFees as $fee) {
            if ($remainingAmount <= 0) break;

            $amountToApply = min($remainingAmount, $fee->balance);

            if ($amountToApply > 0) {
                $this->createPartialPayment($student, $autoPayment, $fee, $amountToApply);
                $remainingAmount -= $amountToApply;
                $appliedFees[] = [
                    'fee_type' => $fee->fee_type,
                    'academic_year' => $fee->academic_year,
                    'term' => $fee->term,
                    'amount' => $amountToApply
                ];
            }
        }

        // Handle any remaining amount as overpayment on the last fee
        if ($remainingAmount > 0) {
            $lastFee = $sortedFees->last();
            $this->createPartialPayment($student, $autoPayment, $lastFee, $remainingAmount, true);
            $appliedFees[] = [
                'fee_type' => $lastFee->fee_type,
                'academic_year' => $lastFee->academic_year,
                'term' => $lastFee->term,
                'amount' => $remainingAmount,
                'is_credit' => true
            ];
        }

        Log::info("Auto-distributed payment {$paymentReference}: " . json_encode($appliedFees));
    }

    /**
     * Apply payment with overpayment (creates credit)
     */
    private function applyPaymentWithOverpayment($student, $autoPayment, $selectedFees, $totalAmount)
    {
        $remainingAmount = $totalAmount;
        $appliedFees = [];

        // Apply to selected fees
        foreach ($selectedFees as $fee) {
            if ($remainingAmount <= 0) break;

            $amountToApply = min($remainingAmount, $fee->balance);

            if ($amountToApply > 0) {
                $this->createPartialPayment($student, $autoPayment, $fee, $amountToApply);
                $remainingAmount -= $amountToApply;
                $appliedFees[] = [
                    'fee_type' => $fee->fee_type,
                    'academic_year' => $fee->academic_year,
                    'term' => $fee->term,
                    'amount' => $amountToApply
                ];
            }
        }

        // Apply remaining as credit to the first fee
        if ($remainingAmount > 0 && $selectedFees->count() > 0) {
            $firstFee = $selectedFees->first();
            $this->createPartialPayment($student, $autoPayment, $firstFee, $remainingAmount, true);
            $appliedFees[] = [
                'fee_type' => $firstFee->fee_type,
                'academic_year' => $firstFee->academic_year,
                'term' => $firstFee->term,
                'amount' => $remainingAmount,
                'is_credit' => true
            ];
        }

        Log::info("Payment with overpayment {$autoPayment->reference_number}: " . json_encode($appliedFees));
    }

    /**
     * Create a partial payment for a specific fee
     */
    private function createPartialPayment($student, $autoPayment, $fee, $amount, $isOverpayment = false)
    {
        $payment = FeePayment::create([
            'fee_id' => $fee->id,
            'student_id' => $student->id,
            'amount' => $amount,
            'payment_method' => $autoPayment->payment_method,
            'reference_number' => $autoPayment->reference_number . '-' . uniqid(),
            'transaction_id' => $autoPayment->transaction_id,
            'payment_date' => $autoPayment->payment_date,
            'status' => 'completed',
            'notes' => 'Payment from ' . $autoPayment->payment_method .
                ($isOverpayment ? ' (Overpayment accepted)' : '') .
                ' - ' . ($autoPayment->narration ?? ''),
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        // Update fee balance
        $fee->paid_amount += $amount;
        $fee->balance = $fee->amount - $fee->paid_amount;

        if ($fee->balance <= 0) {
            $fee->status = $fee->balance < 0 ? 'credit' : 'paid';
        } elseif ($fee->paid_amount > 0) {
            $fee->status = 'partial';
        }

        $fee->save();

        $this->createdPayment = $payment;
        return $payment;
    }

    /**
     * Apply payment to a single fee (normal case)
     */
    private function applyPaymentToSingleFee($student, $autoPayment, $fee, $amount)
    {
        $payment = FeePayment::create([
            'fee_id' => $fee->id,
            'student_id' => $student->id,
            'amount' => $amount,
            'payment_method' => $autoPayment->payment_method,
            'reference_number' => $autoPayment->reference_number,
            'transaction_id' => $autoPayment->transaction_id,
            'payment_date' => $autoPayment->payment_date,
            'status' => 'completed',
            'notes' => 'Verified from ' . $autoPayment->payment_method . ' payment - ' . ($autoPayment->narration ?? ''),
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        // Update fee balance
        $fee->paid_amount += $amount;
        $fee->balance = $fee->amount - $fee->paid_amount;

        if ($fee->balance <= 0) {
            $fee->status = $fee->balance < 0 ? 'credit' : 'paid';
        } elseif ($fee->paid_amount > 0) {
            $fee->status = 'partial';
        }

        $fee->save();

        // Process balance carry-over for next term if needed
        $this->processBalanceCarryOver($student, $fee->academic_year, $fee->term);

        $autoPayment->update([
            'status' => 'verified',
            'matched_student_id' => $student->id,
            'matched_admission_number' => $student->admission_number,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => 'Applied to fee ID: ' . $fee->id,
        ]);

        $this->createdPayment = $payment;
    }

    /**
     * Apply overpayment to a single fee (creates credit)
     */
    private function applyOverpaymentToSingleFee($student, $autoPayment, $fee, $amount)
    {
        $payment = FeePayment::create([
            'fee_id' => $fee->id,
            'student_id' => $student->id,
            'amount' => $amount,
            'payment_method' => $autoPayment->payment_method,
            'reference_number' => $autoPayment->reference_number,
            'transaction_id' => $autoPayment->transaction_id,
            'payment_date' => $autoPayment->payment_date,
            'status' => 'completed',
            'notes' => 'Verified from ' . $autoPayment->payment_method . ' payment - ' . ($autoPayment->narration ?? '') .
                ' | Overpayment accepted, credit balance: KSh ' . number_format(($amount - $fee->balance), 2),
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        // Update fee balance - allow negative balance for credit
        $fee->paid_amount += $amount;
        $fee->balance = $fee->amount - $fee->paid_amount;
        $fee->status = $fee->balance < 0 ? 'credit' : 'paid';
        $fee->save();

        $autoPayment->update([
            'status' => 'verified',
            'matched_student_id' => $student->id,
            'matched_admission_number' => $student->admission_number,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => 'Applied to fee ID: ' . $fee->id . ' with overpayment accepted',
        ]);

        $this->createdPayment = $payment;
    }

    /**
     * Apply payment to multiple fees (when amount exceeds selected fee balance)
     */
    private function applyPaymentToMultipleFees($student, $autoPayment, $primaryFee, $totalAmount, $otherFees)
    {
        $remainingAmount = $totalAmount;
        $appliedFees = [];
        $primaryFeeAmount = min($remainingAmount, $primaryFee->balance);

        // Apply to primary fee first
        if ($primaryFeeAmount > 0) {
            $this->createPartialPayment($student, $autoPayment, $primaryFee, $primaryFeeAmount);
            $remainingAmount -= $primaryFeeAmount;
            $appliedFees[] = "Primary fee: KSh " . number_format($primaryFeeAmount, 2);
        }

        // Apply remaining amount to other fees
        foreach ($otherFees as $fee) {
            if ($remainingAmount <= 0) break;

            $feeAmount = min($remainingAmount, $fee->balance);
            if ($feeAmount > 0) {
                $this->createPartialPayment($student, $autoPayment, $fee, $feeAmount);
                $remainingAmount -= $feeAmount;
                $appliedFees[] = ucfirst(str_replace('_', ' ', $fee->fee_type)) . " fee: KSh " . number_format($feeAmount, 2);
            }
        }

        // Update auto payment record
        $autoPayment->update([
            'status' => 'verified',
            'matched_student_id' => $student->id,
            'matched_admission_number' => $student->admission_number,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => 'Applied to multiple fees: ' . implode(', ', $appliedFees),
        ]);
    }

    /**
     * Apply payment with overpayment (creates credit on last fee) - Legacy method
     */
    private function applyPaymentWithOverpaymentLegacy($student, $autoPayment, $primaryFee, $totalAmount, $otherFees, $overpaymentAmount)
    {
        $remainingAmount = $totalAmount;
        $appliedFees = [];

        // Apply to primary fee
        $primaryFeeAmount = min($remainingAmount, $primaryFee->balance);
        if ($primaryFeeAmount > 0) {
            $this->createPartialPayment($student, $autoPayment, $primaryFee, $primaryFeeAmount);
            $remainingAmount -= $primaryFeeAmount;
            $appliedFees[] = "Primary fee: KSh " . number_format($primaryFeeAmount, 2);
        }

        // Apply to other fees
        foreach ($otherFees as $fee) {
            if ($remainingAmount <= 0) break;

            $feeAmount = min($remainingAmount, $fee->balance);
            if ($feeAmount > 0) {
                $this->createPartialPayment($student, $autoPayment, $fee, $feeAmount);
                $remainingAmount -= $feeAmount;
                $appliedFees[] = ucfirst(str_replace('_', ' ', $fee->fee_type)) . " fee: KSh " . number_format($feeAmount, 2);
            }
        }

        // Apply overpayment to the last fee (create credit)
        if ($remainingAmount > 0) {
            $lastFee = $otherFees->last() ?: $primaryFee;
            $this->createPartialPayment($student, $autoPayment, $lastFee, $remainingAmount, true);
            $appliedFees[] = ucfirst(str_replace('_', ' ', $lastFee->fee_type)) . " fee (credit): KSh " . number_format($remainingAmount, 2);
        }

        $autoPayment->update([
            'status' => 'verified',
            'matched_student_id' => $student->id,
            'matched_admission_number' => $student->admission_number,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => 'Applied with overpayment: ' . implode(', ', $appliedFees),
        ]);
    }

    /**
     * Get receipt data for a payment
     */
    public function getReceiptData(FeePayment $payment)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        $payment->load(['student', 'fee.rank', 'verifiedBy']);

        $feeBreakdown = $this->getFeeBreakdown($payment);

        return response()->json([
            'success' => true,
            'payment' => $payment,
            'fee_breakdown' => $feeBreakdown
        ]);
    }

    /**
     * Generate receipt PDF
     */
    public function generateReceipt(FeePayment $payment)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $payment->load(['student', 'fee.rank', 'verifiedBy']);

        $receiptNumber = 'RCPT-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);

        // Get fee breakdown details
        $feeBreakdown = $this->getFeeBreakdown($payment);

        $data = [
            'payment' => $payment,
            'receipt_number' => $receiptNumber,
            'fee_breakdown' => $feeBreakdown,
            'school' => [
                'name' => config('app.name', 'School Management System'),
                'address' => 'Your School Address',
                'phone' => '+254 XXX XXX XXX',
                'email' => 'info@school.com'
            ]
        ];

        $pdf = PDF::loadView('receipts.fee-payment', $data);

        return $pdf->download("receipt-{$receiptNumber}.pdf");
    }

    /**
     * View receipt in browser
     */
    public function viewReceipt(FeePayment $payment)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $payment->load(['student', 'fee.rank', 'verifiedBy']);

        $receiptNumber = 'RCPT-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);

        // Get fee breakdown details
        $feeBreakdown = $this->getFeeBreakdown($payment);

        return Inertia::render('Admin/Fees/Receipt', [
            'payment' => $payment,
            'receipt_number' => $receiptNumber,
            'fee_breakdown' => $feeBreakdown,
            'school' => [
                'name' => config('app.name', 'School Management System'),
                'address' => 'Your School Address',
                'phone' => '+254 XXX XXX XXX',
                'email' => 'info@school.com'
            ]
        ]);
    }

    private function getFeeBreakdown(FeePayment $payment)
    {
        $student = $payment->student;
        $currentFee = $payment->fee;

        // Get previous term balances (carry-over)
        $previousBalances = Fee::where('student_id', $student->id)
            ->where(function ($query) use ($currentFee) {
                // Previous terms in same academic year
                $query->where('academic_year', $currentFee->academic_year)
                    ->where('term', '<', $currentFee->term)
                    // Or previous academic years
                    ->orWhere('academic_year', '<', $currentFee->academic_year);
            })
            ->where('balance', '!=', 0)
            ->get();

        $breakdown = [];
        $totalPreviousBalance = 0;
        $totalPreviousCredit = 0;

        // Add previous balances
        foreach ($previousBalances as $previousFee) {
            if ($previousFee->balance > 0) {
                $breakdown[] = [
                    'description' => 'Balance from ' . $previousFee->academic_year . ' Term ' . $previousFee->term . ' - ' . ucfirst(str_replace('_', ' ', $previousFee->fee_type)),
                    'amount' => $previousFee->balance,
                    'type' => 'previous_balance'
                ];
                $totalPreviousBalance += $previousFee->balance;
            } else {
                $breakdown[] = [
                    'description' => 'Credit from ' . $previousFee->academic_year . ' Term ' . $previousFee->term . ' - ' . ucfirst(str_replace('_', ' ', $previousFee->fee_type)),
                    'amount' => abs($previousFee->balance),
                    'type' => 'previous_credit'
                ];
                $totalPreviousCredit += abs($previousFee->balance);
            }
        }

        // Add current term fees
        $currentTermFees = Fee::where('student_id', $student->id)
            ->where('academic_year', $currentFee->academic_year)
            ->where('term', $currentFee->term)
            ->get();

        foreach ($currentTermFees as $fee) {
            $breakdown[] = [
                'description' => ucfirst(str_replace('_', ' ', $fee->fee_type)) . ' Fee - Term ' . $fee->term . ' ' . $fee->academic_year,
                'amount' => $fee->amount,
                'type' => 'current_fee'
            ];
        }

        // Calculate totals
        $totalCurrentFees = $currentTermFees->sum('amount');
        $totalBalanceBeforePayment = $totalPreviousBalance + $totalCurrentFees - $totalPreviousCredit;
        $newBalance = $totalBalanceBeforePayment - $payment->amount;

        return [
            'breakdown' => $breakdown,
            'summary' => [
                'total_previous_balance' => $totalPreviousBalance,
                'total_previous_credit' => $totalPreviousCredit,
                'total_current_fees' => $totalCurrentFees,
                'total_balance_before' => $totalBalanceBeforePayment,
                'amount_paid' => $payment->amount,
                'new_balance' => $newBalance
            ]
        ];
    }

    public function recordedPayments(Request $request)
    {
        // Check authentication
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $payments = AutoRecordedPayment::with(['student'])
            ->when($request->has('status') && $request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->has('payment_method') && $request->payment_method, function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })
            ->when($request->has('date_from') && $request->date_from, function ($query) use ($request) {
                $query->where('payment_date', '>=', $request->date_from);
            })
            ->when($request->has('date_to') && $request->date_to, function ($query) use ($request) {
                $query->where('payment_date', '<=', $request->date_to);
            })
            ->latest()
            ->paginate(20);

        $stats = [
            'recorded' => AutoRecordedPayment::where('status', 'recorded')->count(),
            'unmatched' => AutoRecordedPayment::where('status', 'unmatched')->count(),
            'verified' => AutoRecordedPayment::where('status', 'verified')->count(),
            'rejected' => AutoRecordedPayment::where('status', 'rejected')->count(),
            'total' => AutoRecordedPayment::count(),
        ];

        return Inertia::render('Admin/Fees/PaymentVerification/Index', [
            'payments' => $payments,
            'stats' => $stats,
            'filters' => $request->only(['status', 'payment_method', 'date_from', 'date_to']),
        ]);
    }

    public function matchPayment(Request $request, AutoRecordedPayment $payment)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        $request->validate([
            'admission_number' => 'required|exists:students,admission_number',
        ]);

        $student = Student::where('admission_number', $request->admission_number)->first();

        $payment->update([
            'matched_student_id' => $student->id,
            'matched_admission_number' => $student->admission_number,
            'status' => 'recorded',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment matched to student successfully!'
        ]);
    }

    public function rejectPayment(Request $request, AutoRecordedPayment $payment)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        $request->validate([
            'reason' => 'required|string'
        ]);

        try {
            $payment->update([
                'status' => 'rejected',
                'verification_notes' => $request->reason,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            Log::info("Payment rejected: {$payment->reference_number} - {$request->reason}");

            return response()->json([
                'success' => true,
                'message' => 'Payment rejected successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Payment rejection error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error rejecting payment: ' . $e->getMessage()
            ]);
        }
    }

    public function autoMatchPayments()
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        try {
            $unmatchedPayments = AutoRecordedPayment::where('status', 'recorded')
                ->whereNull('matched_student_id')
                ->get();

            $matchedCount = 0;

            foreach ($unmatchedPayments as $payment) {
                // Try to extract admission number from narration
                $admissionNumber = $this->extractAdmissionNumber($payment->narration);

                if ($admissionNumber) {
                    $student = Student::where('admission_number', $admissionNumber)->first();

                    if ($student) {
                        $payment->update([
                            'matched_student_id' => $student->id,
                            'matched_admission_number' => $student->admission_number,
                            'status' => 'recorded', // Keep as recorded for manual verification
                        ]);
                        $matchedCount++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Auto-matched {$matchedCount} payments",
                'matched_count' => $matchedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Auto-match payments error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error auto-matching payments: ' . $e->getMessage()
            ]);
        }
    }

    private function extractAdmissionNumber($narration)
    {
        if (!$narration) return null;

        // Common patterns for admission numbers
        preg_match_all('/(ADM|adm|Adm)\s?(\d+)/i', $narration, $matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                // Clean up the admission number
                $cleanNumber = strtoupper(preg_replace('/\s+/', '', $match));
                return $cleanNumber;
            }
        }

        // Try other patterns
        preg_match_all('/\b\d{4,}\b/', $narration, $numberMatches);
        foreach ($numberMatches[0] as $number) {
            if (strlen($number) >= 4) {
                $student = Student::where('admission_number', 'like', "%{$number}%")->first();
                if ($student) return $student->admission_number;
            }
        }

        return null;
    }

    public function processBalanceCarryOver(Student $student, $academicYear, $term)
    {
        try {
            DB::transaction(function () use ($student, $academicYear, $term) {
                // Get previous term/year balances
                $previousBalances = Fee::where('student_id', $student->id)
                    ->where(function ($query) use ($academicYear, $term) {
                        // Previous terms in same academic year
                        $query->where('academic_year', $academicYear)
                            ->where('term', '<', $term)
                            // Or previous academic years
                            ->orWhere('academic_year', '<', $academicYear);
                    })
                    ->where('balance', '!=', 0)
                    ->get();

                $totalCarryOver = 0;
                $totalCreditCarryOver = 0;

                foreach ($previousBalances as $previousFee) {
                    if ($previousFee->balance > 0) {
                        $totalCarryOver += $previousFee->balance;
                    } else {
                        $totalCreditCarryOver += abs($previousFee->balance);
                    }

                    // Mark as carried over
                    $previousFee->update([
                        'is_carry_over' => true,
                        'status' => $previousFee->balance > 0 ? 'carried_over' : 'credit_carried'
                    ]);
                }

                // Calculate net carry over (balance - credit)
                $netCarryOver = $totalCarryOver - $totalCreditCarryOver;

                // If there's a net balance to carry over, create or update current term fee
                if ($netCarryOver != 0) {
                    $currentFee = Fee::where('student_id', $student->id)
                        ->where('academic_year', $academicYear)
                        ->where('term', $term)
                        ->where('fee_type', 'tuition')
                        ->first();

                    if ($currentFee) {
                        // Update existing fee with carry-over
                        $newBalance = $currentFee->balance + $netCarryOver;
                        $currentFee->update([
                            'amount' => $currentFee->amount + $netCarryOver,
                            'balance' => $newBalance,
                            'is_carry_over' => $netCarryOver != 0,
                            'description' => $currentFee->description .
                                ($netCarryOver > 0 ?
                                    " (Includes KSh " . number_format($netCarryOver, 2) . " previous balance)" :
                                    " (Includes KSh " . number_format(abs($netCarryOver), 2) . " credit)")
                        ]);
                    }
                }
            });

            return $netCarryOver ?? 0;
        } catch (\Exception $e) {
            Log::error('Balance carry-over error for student ' . $student->id . ': ' . $e->getMessage());
            return 0;
        }
    }

    public function index(Request $request)
    {
        // Check authentication
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $payments = FeePayment::with(['student', 'fee.rank', 'verifiedBy'])
            ->when($request->has('payment_method') && $request->payment_method, function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })
            ->when($request->has('status') && $request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->has('date_from') && $request->date_from, function ($query) use ($request) {
                $query->where('payment_date', '>=', $request->date_from);
            })
            ->when($request->has('date_to') && $request->date_to, function ($query) use ($request) {
                $query->where('payment_date', '<=', $request->date_to);
            })
            ->latest()
            ->paginate(20);

        $stats = $this->getPaymentStatsData();

        return Inertia::render('Admin/Fees/PaymentHistory', [
            'payments' => $payments,
            'filters' => $request->only(['payment_method', 'status', 'date_from', 'date_to']),
            'stats' => $stats
        ]);
    }

    private function getPaymentStatsData()
    {
        try {
            $totalPayments = FeePayment::where('status', 'completed')->count();
            $totalAmount = FeePayment::where('status', 'completed')->sum('amount');

            $todayPayments = FeePayment::where('status', 'completed')
                ->whereDate('payment_date', today())
                ->count();
            $todayAmount = FeePayment::where('status', 'completed')
                ->whereDate('payment_date', today())
                ->sum('amount');

            $weekPayments = FeePayment::where('status', 'completed')
                ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();
            $weekAmount = FeePayment::where('status', 'completed')
                ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('amount');

            $monthPayments = FeePayment::where('status', 'completed')
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->count();
            $monthAmount = FeePayment::where('status', 'completed')
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount');

            return [
                'total_payments' => $totalPayments,
                'total_amount' => $totalAmount,
                'today_payments' => $todayPayments,
                'today_amount' => $todayAmount,
                'week_payments' => $weekPayments,
                'week_amount' => $weekAmount,
                'month_payments' => $monthPayments,
                'month_amount' => $monthAmount,
                'average_payment' => $totalPayments > 0 ? round($totalAmount / $totalPayments, 2) : 0
            ];
        } catch (\Exception $e) {
            return [
                'total_payments' => 0,
                'total_amount' => 0,
                'today_payments' => 0,
                'today_amount' => 0,
                'week_payments' => 0,
                'week_amount' => 0,
                'month_payments' => 0,
                'month_amount' => 0,
                'average_payment' => 0
            ];
        }
    }

    public function receipt(FeePayment $payment)
    {
        // Check authentication
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $payment->load(['student', 'fee.rank', 'verifiedBy']);

        $receiptNumber = 'RCPT-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);

        return Inertia::render('Admin/Fees/Receipt', [
            'payment' => $payment,
            'receipt_number' => $receiptNumber,
        ]);
    }

    public function reversePayment(Request $request, FeePayment $payment)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        $request->validate([
            'reason' => 'required|string'
        ]);

        try {
            DB::transaction(function () use ($request, $payment) {
                if ($payment->status === 'reversed') {
                    throw new \Exception('Payment has already been reversed.');
                }

                $fee = $payment->fee;

                $fee->paid_amount -= $payment->amount;
                $fee->balance = $fee->amount - $fee->paid_amount;

                if ($fee->paid_amount <= 0) {
                    $fee->status = 'pending';
                } elseif ($fee->balance > 0) {
                    $fee->status = 'partial';
                } elseif ($fee->balance < 0) {
                    $fee->status = 'credit_reversed';
                }

                $fee->save();

                $payment->update([
                    'status' => 'reversed',
                    'reversed_by' => Auth::id(),
                    'reversed_at' => now(),
                    'notes' => $payment->notes . "\n\nREVERSED: " . $request->reason . " - " . now()->format('Y-m-d H:i:s') . " by " . Auth::user()->name
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment reversed successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error reversing payment: ' . $e->getMessage()
            ]);
        }
    }

    public function dataTable(Request $request)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }

        $payments = FeePayment::with(['student', 'fee.rank', 'verifiedBy', 'reversedBy'])
            ->when($request->has('payment_method') && $request->payment_method, function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })
            ->when($request->has('status') && $request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->has('date_from') && $request->date_from, function ($query) use ($request) {
                $query->where('payment_date', '>=', $request->date_from);
            })
            ->when($request->has('date_to') && $request->date_to, function ($query) use ($request) {
                $query->where('payment_date', '<=', $request->date_to);
            })
            ->when($request->has('student_name') && $request->student_name, function ($query) use ($request) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('full_name', 'like', '%' . $request->student_name . '%');
                });
            })
            ->latest();

        return datatables()->eloquent($payments)
            ->addColumn('student_name', function ($payment) {
                return $payment->student->full_name;
            })
            ->addColumn('admission_number', function ($payment) {
                return $payment->student->admission_number;
            })
            ->addColumn('class', function ($payment) {
                return $payment->fee->rank->name;
            })
            ->addColumn('fee_type', function ($payment) {
                return ucfirst(str_replace('_', ' ', $payment->fee->fee_type)) . ' Fee';
            })
            ->addColumn('verified_by_name', function ($payment) {
                return $payment->verifiedBy->name ?? 'N/A';
            })
            ->addColumn('reversed_by_name', function ($payment) {
                return $payment->reversedBy->name ?? 'N/A';
            })
            ->addColumn('receipt_number', function ($payment) {
                return 'RCPT-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);
            })
            ->addColumn('status_badge', function ($payment) {
                $badgeClass = [
                    'completed' => 'bg-success',
                    'pending' => 'bg-warning',
                    'failed' => 'bg-danger',
                    'reversed' => 'bg-secondary'
                ][$payment->status] ?? 'bg-secondary';

                return '<span class="badge ' . $badgeClass . '">' . ucfirst($payment->status) . '</span>';
            })
            ->addColumn('actions', function ($payment) {
                $actions = '
                    <a href="' . route('admin.fees.payments.receipt', $payment->id) . '" 
                       class="btn btn-sm btn-primary" 
                       target="_blank"
                       title="View Receipt">
                        <i class="fas fa-receipt"></i>
                    </a>
                ';

                if ($payment->status === 'completed') {
                    $actions .= '
                        <button class="btn btn-sm btn-warning reverse-payment" 
                                data-id="' . $payment->id . '"
                                data-student="' . $payment->student->full_name . '"
                                data-amount="' . $payment->amount . '"
                                title="Reverse Payment">
                            <i class="fas fa-undo"></i>
                        </button>
                    ';
                }

                return '<div class="btn-group">' . $actions . '</div>';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->toJson();
    }

    public function getPaymentStats()
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        try {
            $totalPayments = FeePayment::where('status', 'completed')->count();
            $totalAmount = FeePayment::where('status', 'completed')->sum('amount');

            $todayPayments = FeePayment::where('status', 'completed')
                ->whereDate('payment_date', today())
                ->count();
            $todayAmount = FeePayment::where('status', 'completed')
                ->whereDate('payment_date', today())
                ->sum('amount');

            $weekPayments = FeePayment::where('status', 'completed')
                ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();
            $weekAmount = FeePayment::where('status', 'completed')
                ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('amount');

            $monthPayments = FeePayment::where('status', 'completed')
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->count();
            $monthAmount = FeePayment::where('status', 'completed')
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount');

            $paymentMethods = FeePayment::where('status', 'completed')
                ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->groupBy('payment_method')
                ->get();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_payments' => $totalPayments,
                    'total_amount' => $totalAmount,
                    'today_payments' => $todayPayments,
                    'today_amount' => $todayAmount,
                    'week_payments' => $weekPayments,
                    'week_amount' => $weekAmount,
                    'month_payments' => $monthPayments,
                    'month_amount' => $monthAmount,
                    'payment_methods' => $paymentMethods,
                    'average_payment' => $totalPayments > 0 ? round($totalAmount / $totalPayments, 2) : 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment statistics: ' . $e->getMessage(),
                'stats' => [
                    'total_payments' => 0,
                    'total_amount' => 0,
                    'today_payments' => 0,
                    'today_amount' => 0,
                    'week_payments' => 0,
                    'week_amount' => 0,
                    'month_payments' => 0,
                    'month_amount' => 0,
                    'payment_methods' => [],
                    'average_payment' => 0
                ]
            ]);
        }
    }

    public function getStudentPayments(Student $student)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        try {
            $payments = FeePayment::with(['fee.rank', 'verifiedBy'])
                ->where('student_id', $student->id)
                ->latest()
                ->get();

            $totalPaid = $payments->where('status', 'completed')->sum('amount');
            $totalReversed = $payments->where('status', 'reversed')->sum('amount');

            return response()->json([
                'success' => true,
                'payments' => $payments,
                'student' => $student->load('currentRank'),
                'summary' => [
                    'total_paid' => $totalPaid,
                    'total_reversed' => $totalReversed,
                    'net_paid' => $totalPaid - $totalReversed,
                    'payment_count' => $payments->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student payments: ' . $e->getMessage()
            ]);
        }
    }

    public function getStudentFeeStatement(Student $student)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        try {
            $fees = Fee::with(['rank'])
                ->where('student_id', $student->id)
                ->latest()
                ->get();

            $payments = FeePayment::with(['fee.rank', 'verifiedBy'])
                ->where('student_id', $student->id)
                ->latest()
                ->get();

            $totalFees = $fees->sum('amount');
            $totalPaid = $payments->where('status', 'completed')->sum('amount');
            $totalBalance = $fees->sum('balance');

            return response()->json([
                'success' => true,
                'fees' => $fees,
                'payments' => $payments,
                'student' => $student->load('currentRank'),
                'summary' => [
                    'total_fees' => $totalFees,
                    'total_paid' => $totalPaid,
                    'total_balance' => $totalBalance,
                    'fee_count' => $fees->count(),
                    'payment_count' => $payments->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student fee statement: ' . $e->getMessage()
            ]);
        }
    }

    public function getPaymentAnalytics(Request $request)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        try {
            $period = $request->get('period', 'month');

            if ($period === 'week') {
                $data = FeePayment::where('status', 'completed')
                    ->whereBetween('payment_date', [now()->subWeek(), now()])
                    ->selectRaw('DATE(payment_date) as date, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
            } elseif ($period === 'month') {
                $data = FeePayment::where('status', 'completed')
                    ->whereBetween('payment_date', [now()->subMonth(), now()])
                    ->selectRaw('DATE(payment_date) as date, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
            } else {
                $data = FeePayment::where('status', 'completed')
                    ->whereYear('payment_date', now()->year)
                    ->selectRaw('MONTH(payment_date) as month, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'period' => $period,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment analytics: ' . $e->getMessage()
            ]);
        }
    }

    public function getVerificationStats()
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        $stats = [
            'recorded' => AutoRecordedPayment::where('status', 'recorded')->count(),
            'unmatched' => AutoRecordedPayment::where('status', 'unmatched')->count(),
            'verified' => AutoRecordedPayment::where('status', 'verified')->count(),
            'rejected' => AutoRecordedPayment::where('status', 'rejected')->count(),
            'total' => AutoRecordedPayment::count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    public function searchStudents(Request $request)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        try {
            $query = $request->get('query');

            $students = Student::with(['currentRank'])
                ->where('admission_number', 'like', "%{$query}%")
                ->orWhere('full_name', 'like', "%{$query}%")
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'students' => $students
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching students: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Transfer credit balance to another student
     */
    public function transferCredit(Request $request)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.'
            ], 401);
        }

        $request->validate([
            'from_student_id' => 'required|exists:students,id',
            'to_student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $fromStudent = Student::findOrFail($request->from_student_id);
                $toStudent = Student::findOrFail($request->to_student_id);
                $amount = $request->amount;

                // Check if from student has sufficient credit
                $totalCredit = Fee::where('student_id', $fromStudent->id)
                    ->where('balance', '<', 0)
                    ->sum('balance');

                $availableCredit = abs($totalCredit);

                if ($amount > $availableCredit) {
                    throw new \Exception('Insufficient credit balance. Available credit: KSh ' . number_format($availableCredit, 2));
                }

                // Find credit fees to transfer from
                $creditFees = Fee::where('student_id', $fromStudent->id)
                    ->where('balance', '<', 0)
                    ->orderBy('balance', 'asc') // Most negative first
                    ->get();

                $remainingAmount = $amount;

                foreach ($creditFees as $creditFee) {
                    if ($remainingAmount <= 0) break;

                    $transferable = min(abs($creditFee->balance), $remainingAmount);

                    // Reduce credit from source fee
                    $creditFee->paid_amount -= $transferable;
                    $creditFee->balance = $creditFee->amount - $creditFee->paid_amount;

                    if ($creditFee->balance >= 0) {
                        $creditFee->status = $creditFee->balance == 0 ? 'paid' : 'partial';
                    }

                    $creditFee->save();

                    // Find or create a fee for the destination student to apply the credit
                    $currentTermFee = Fee::where('student_id', $toStudent->id)
                        ->where('academic_year', now()->year)
                        ->where('term', $this->getCurrentTerm())
                        ->where('fee_type', 'tuition')
                        ->first();

                    if (!$currentTermFee) {
                        $currentTermFee = Fee::create([
                            'student_id' => $toStudent->id,
                            'rank_id' => $toStudent->current_rank_id,
                            'fee_type' => 'tuition',
                            'amount' => 0, // Will be updated
                            'paid_amount' => 0,
                            'balance' => 0,
                            'academic_year',
                            now()->year,
                            'term' => $this->getCurrentTerm(),
                            'due_date' => now()->addMonth(),
                            'status' => 'pending',
                            'description' => 'Tuition fee with transferred credit'
                        ]);
                    }

                    // Apply credit to destination fee
                    $currentTermFee->paid_amount += $transferable;
                    $currentTermFee->balance = $currentTermFee->amount - $currentTermFee->paid_amount;

                    if ($currentTermFee->balance < 0) {
                        $currentTermFee->status = 'credit';
                    } elseif ($currentTermFee->balance == 0) {
                        $currentTermFee->status = 'paid';
                    } else {
                        $currentTermFee->status = 'partial';
                    }

                    $currentTermFee->save();

                    // Create transfer record
                    FeePayment::create([
                        'fee_id' => $currentTermFee->id,
                        'student_id' => $toStudent->id,
                        'amount' => $transferable,
                        'payment_method' => 'credit_transfer',
                        'reference_number' => 'CT-' . uniqid(),
                        'payment_date' => now(),
                        'status' => 'completed',
                        'notes' => 'Credit transfer from ' . $fromStudent->admission_number .
                            ' - Reason: ' . $request->reason .
                            ' - Transferred by: ' . Auth::user()->name,
                        'verified_by' => Auth::id(),
                        'verified_at' => now(),
                    ]);

                    $remainingAmount -= $transferable;
                }

                Log::info("Credit transfer: KSh " . number_format($amount, 2) .
                    " from student " . $fromStudent->admission_number .
                    " to student " . $toStudent->admission_number .
                    " by user " . Auth::user()->name);
            });

            return response()->json([
                'success' => true,
                'message' => 'Credit transferred successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Credit transfer error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error transferring credit: ' . $e->getMessage()
            ]);
        }
    }

    private function getCurrentTerm()
    {
        $month = now()->month;

        if ($month >= 1 && $month <= 4) return 1;
        if ($month >= 5 && $month <= 8) return 2;
        return 3;
    }
}
