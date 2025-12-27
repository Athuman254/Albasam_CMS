<?php

namespace App\Services;

use App\Models\AutoRecordedPayment;
use App\Models\Student;
use App\Models\Fee;
use App\Models\FeeStructure;
use App\Models\FeePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentAllocationService
{
    public function allocatePaymentToStudent(AutoRecordedPayment $payment, Student $student): array
    {
        try {
            DB::beginTransaction();

            if (!$this->canBeAllocatedTo($payment, $student)) {
                Log::warning("Payment cannot be allocated to student", [
                    'payment_id' => $payment->id,
                    'student_id' => $student->id,
                    'payment_status' => $payment->status,
                    'matched_student_id' => $payment->matched_student_id,
                    'payment_amount' => $payment->amount
                ]);

                DB::commit();
                return [
                    'success' => false,
                    'allocated_amount' => 0,
                    'remaining_amount' => $payment->amount,
                    'message' => 'Payment cannot be allocated to this student'
                ];
            }

            $totalAllocated = $payment->feePayments()->sum('amount');
            $Balance = $payment->amount - $totalAllocated;
            $allocatedAmount = 0;
            $allocatedFees = [];


            $unpaidFees = $this->getUnpaidFeesByAge($student);

            Log::info("Allocating payment for student {$student->admission_number}", [
                'payment_id' => $payment->id,
                'payment_amount' => $payment->amount,
                'already_allocated' => $totalAllocated,
                'remaining_amount' => $Balance,
                'unpaid_fees_count' => $unpaidFees->count(),
                'student_name' => $student->full_name
            ]);

            foreach ($unpaidFees as $fee) {
                if ($Balance <= 0) {
                    break;
                }

                $amountToAllocate = min($Balance, $fee->balance);

                if ($amountToAllocate > 0) {
                    $feePayment = $this->allocateToFee($fee, $amountToAllocate, $payment);

                    if ($feePayment) {
                        $allocatedAmount += $amountToAllocate;
                        $Balance -= $amountToAllocate;
                        $allocatedFees[] = [
                            'fee_id' => $fee->id,
                            'fee_type' => $fee->fee_type,
                            'academic_year' => $fee->academic_year,
                            'term' => $fee->term,
                            'amount_allocated' => $amountToAllocate,
                            'remaining_balance' => $fee->fresh()->balance,
                            'payment_id' => $feePayment->id // Added payment_id for receipt
                        ];

                        Log::info("Allocated {$amountToAllocate} to fee {$fee->id}", [
                            'fee_type' => $fee->fee_type,
                            'academic_year' => $fee->academic_year,
                            'term' => $fee->term,
                            'remaining_balance' => $fee->fresh()->balance
                        ]);
                    }
                }
            }

            // If balance remains, allocate as credit/overpayment to ensure payment visibility
            if ($Balance > 0) {
                $currentYear = date('Y');
                $currentTerm = $this->getCurrentTerm();

                // Find an existing Credit fee or create a new one
                $creditFee = Fee::where('student_id', $student->id)
                    ->where('fee_type', 'other')
                    ->where('academic_year', $currentYear)
                    ->where('term', $currentTerm)
                    ->where('description', 'LIKE', '%Credit%')
                    ->first();

                if (!$creditFee) {
                    $creditFee = Fee::create([
                        'student_id' => $student->id,
                        'rank_id' => $student->rank_id,
                        'fee_type' => 'other',
                        'amount' => 0,
                        'paid_amount' => 0,
                        'balance' => 0,
                        'academic_year' => $currentYear,
                        'term' => $currentTerm,
                        'due_date' => now(),
                        'status' => 'paid',
                        'description' => 'Credit/Overpayment Balance',
                        'is_carry_over' => false
                    ]);
                }

                $feePayment = $this->allocateToFee($creditFee, $Balance, $payment);
                if ($feePayment) {
                    $allocatedAmount += $Balance;
                    $allocatedFees[] = [
                        'fee_id' => $creditFee->id,
                        'fee_type' => 'credit',
                        'academic_year' => $creditFee->academic_year,
                        'term' => $creditFee->term,
                        'amount_allocated' => $Balance,
                        'remaining_balance' => $creditFee->fresh()->balance,
                        'payment_id' => $feePayment->id
                    ];
                    $Balance = 0;
                }
            }

            $this->updatePaymentStatus($payment, $allocatedAmount);

            DB::commit();


            $isFullyAllocated = ($allocatedAmount + $totalAllocated) >= $payment->amount;

            Log::info("Payment allocation completed", [
                'payment_id' => $payment->id,
                'allocated_amount' => $allocatedAmount,
                'remaining_amount' => $Balance,
                'student_id' => $student->id,
                'allocated_fees_count' => count($allocatedFees),
                'fully_allocated' => $isFullyAllocated
            ]);

            return [
                'success' => true,
                'allocated_amount' => $allocatedAmount,
                'remaining_amount' => $Balance,
                'fully_allocated' => $isFullyAllocated,
                'allocated_fees' => $allocatedFees,
                'unallocated_fees_count' => $unpaidFees->where('balance', '>', 0)->count(),
                'message' => $allocatedAmount > 0 ?
                    "Successfully allocated KSh {$allocatedAmount} to {$student->admission_number}" :
                    "No allocation made - student has no unpaid fees"
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment allocation failed", [
                'payment_id' => $payment->id,
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'allocated_amount' => 0,
                'remaining_amount' => $payment->amount,
                'message' => 'Payment allocation failed: ' . $e->getMessage()
            ];
        }
    }

    private function getCurrentTerm(): int
    {
        $month = now()->month;

        if ($month >= 1 && $month <= 4) return 1;
        if ($month >= 5 && $month <= 8) return 2;
        return 3;
    }


    private function canBeAllocatedTo(AutoRecordedPayment $payment, Student $student): bool
    {
        if (!in_array($payment->status, ['recorded', 'unmatched'])) {
            return false;
        }

        if ($payment->matched_student_id !== $student->id) {
            return false;
        }

        $totalAllocated = $payment->feePayments()->sum('amount');
        return ($payment->amount - $totalAllocated) > 0;
    }


    private function getUnpaidFeesByAge(Student $student)
    {
        return Fee::where('student_id', $student->id)
            ->where('balance', '>', 0)
            ->orderBy('academic_year', 'asc')
            ->orderBy('term', 'asc')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->with(['rank'])
            ->get();
    }


    private function allocateToFee(Fee $fee, float $amount, AutoRecordedPayment $payment): ?FeePayment
    {

        $existingAllocation = FeePayment::where('auto_recorded_payment_id', $payment->id)
            ->where('fee_id', $fee->id)
            ->exists();

        if ($existingAllocation) {
            Log::warning("Duplicate allocation attempt prevented", [
                'payment_id' => $payment->id,
                'fee_id' => $fee->id
            ]);
            return null;
        }


        $feePayment = FeePayment::create([
            'fee_id' => $fee->id,
            'student_id' => $fee->student_id,
            'amount' => $amount,
            'payment_method' => $payment->payment_method,
            'reference_number' => $payment->reference_number,
            'transaction_id' => $payment->transaction_id,
            'payment_date' => $payment->payment_date,
            'status' => 'completed',
            'notes' => "Auto-allocated from {$payment->payment_method} payment #{$payment->reference_number}",
            'verified_by' => null,
            'verified_at' => now(),
            'auto_recorded_payment_id' => $payment->id,
        ]);

        $fee->paid_amount += $amount;
        $fee->balance -= $amount;


        if ($fee->balance <= 0) {
            $fee->status = 'paid';
        } elseif ($fee->paid_amount > 0) {
            $fee->status = 'partial';
        }

        $fee->save();

        return $feePayment;
    }


    private function updatePaymentStatus(AutoRecordedPayment $payment, float $allocatedAmount): void
    {
        if ($allocatedAmount > 0) {
            $totalAllocated = $payment->feePayments()->sum('amount');
            $isFullyAllocated = $totalAllocated >= $payment->amount;

            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'verification_notes' => $isFullyAllocated ?
                    "Fully allocated KSh {$totalAllocated}" :
                    "Partially allocated KSh {$totalAllocated} of KSh {$payment->amount}"
            ]);
        }
    }


    public function processUnmatchedPayments(): array
    {
        $unmatchedPayments = AutoRecordedPayment::where('status', 'unmatched')
            ->whereNotNull('matched_admission_number')
            ->get();

        $processedCount = 0;
        $results = [];

        foreach ($unmatchedPayments as $payment) {
            try {
                $student = Student::where('admission_number', $payment->matched_admission_number)->first();

                if ($student) {
                    $this->generateStudentFees($student);

                    $result = $this->allocatePaymentToStudent($payment, $student);

                    if ($result['success'] && $result['allocated_amount'] > 0) {
                        $processedCount++;
                    }

                    $results[] = [
                        'payment_id' => $payment->id,
                        'admission_number' => $payment->matched_admission_number,
                        'result' => $result
                    ];
                }
            } catch (\Exception $e) {
                Log::error("Failed to process unmatched payment", [
                    'payment_id' => $payment->id,
                    'admission_number' => $payment->matched_admission_number,
                    'error' => $e->getMessage()
                ]);

                $results[] = [
                    'payment_id' => $payment->id,
                    'admission_number' => $payment->matched_admission_number,
                    'result' => ['success' => false, 'message' => $e->getMessage()]
                ];
            }
        }

        return [
            'processed_count' => $processedCount,
            'total_unmatched' => $unmatchedPayments->count(),
            'results' => $results
        ];
    }

    public function generateStudentFees(Student $student): int
    {
        $currentRank = $student->currentRank;

        if (!$currentRank) {
            Log::warning("Student has no current rank", ['student_id' => $student->id]);
            return 0;
        }

        $feeStructures = FeeStructure::where('rank_id', $currentRank->id)
            ->where('is_active', true)
            ->get();

        $generatedCount = 0;

        foreach ($feeStructures as $feeStructure) {

            $existingFee = Fee::where('student_id', $student->id)
                ->where('rank_id', $feeStructure->rank_id)
                ->where('academic_year', $feeStructure->academic_year)
                ->where('term', $feeStructure->term)
                ->first();

            if (!$existingFee) {

                Fee::create([
                    'student_id' => $student->id,
                    'rank_id' => $feeStructure->rank_id,
                    'original_fee_structure_id' => $feeStructure->id,
                    'fee_type' => 'tuition',
                    'amount' => $feeStructure->amount,
                    'paid_amount' => 0,
                    'balance' => $feeStructure->amount,
                    'academic_year' => $feeStructure->academic_year,
                    'term' => $feeStructure->term,
                    'due_date' => $feeStructure->due_date,
                    'status' => 'pending',
                    'is_carry_over' => false,
                    'description' => $feeStructure->description,
                ]);

                $generatedCount++;


                if ($feeStructure->additional_fees) {
                    foreach ($feeStructure->additional_fees as $additionalFee) {
                        Fee::create([
                            'student_id' => $student->id,
                            'rank_id' => $feeStructure->rank_id,
                            'original_fee_structure_id' => $feeStructure->id,
                            'fee_type' => $additionalFee['fee_type'] ?? 'other',
                            'amount' => $additionalFee['amount'],
                            'paid_amount' => 0,
                            'balance' => $additionalFee['amount'],
                            'academic_year' => $feeStructure->academic_year,
                            'term' => $feeStructure->term,
                            'due_date' => $feeStructure->due_date,
                            'status' => 'pending',
                            'is_carry_over' => false,
                            'description' => $additionalFee['description'] ?? $additionalFee['name'],
                        ]);

                        $generatedCount++;
                    }
                }
            }
        }

        Log::info("Generated {$generatedCount} fees for student", [
            'student_id' => $student->id,
            'rank_id' => $currentRank->id
        ]);

        return $generatedCount;
    }


    public function getAllocationSummary(AutoRecordedPayment $payment): array
    {
        $feePayments = $payment->feePayments()->with(['fee', 'student'])->get();
        $totalAllocated = $feePayments->sum('amount');
        $remainingAmount = $payment->amount - $totalAllocated;
        $isFullyAllocated = $remainingAmount <= 0;

        return [
            'payment' => $payment,
            'allocations' => $feePayments,
            'total_allocated' => $totalAllocated,
            'remaining_amount' => $remainingAmount,
            'is_fully_allocated' => $isFullyAllocated,
            'allocation_percentage' => $payment->amount > 0 ?
                round(($totalAllocated / $payment->amount) * 100, 2) : 0
        ];
    }

    public function getAllocationStatistics(): array
    {
        $totalPayments = AutoRecordedPayment::count();
        $verifiedPayments = AutoRecordedPayment::where('status', 'verified')->count();
        $unmatchedPayments = AutoRecordedPayment::where('status', 'unmatched')->count();
        $recordedPayments = AutoRecordedPayment::where('status', 'recorded')->count();

        $totalAllocated = FeePayment::whereNotNull('auto_recorded_payment_id')->sum('amount');
        $recentAllocations = FeePayment::whereNotNull('auto_recorded_payment_id')
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('amount');

        return [
            'total_payments' => $totalPayments,
            'verified_payments' => $verifiedPayments,
            'unmatched_payments' => $unmatchedPayments,
            'recorded_payments' => $recordedPayments,
            'total_allocated' => $totalAllocated,
            'recent_allocations' => $recentAllocations,
            'allocation_rate' => $totalPayments > 0 ? round(($verifiedPayments / $totalPayments) * 100, 2) : 0,
        ];
    }


    public function manualAllocation(AutoRecordedPayment $payment, Student $student, array $feeAllocations): array
    {
        try {
            DB::beginTransaction();

            $totalAllocated = 0;
            $allocatedFees = [];

            foreach ($feeAllocations as $allocation) {
                $fee = Fee::find($allocation['fee_id']);

                if (!$fee || $fee->student_id !== $student->id) {
                    throw new \Exception("Invalid fee ID or fee doesn't belong to student");
                }

                $amountToAllocate = min($allocation['amount'], $fee->balance);

                if ($amountToAllocate > 0) {
                    $feePayment = $this->allocateToFee($fee, $amountToAllocate, $payment);

                    if ($feePayment) {
                        $totalAllocated += $amountToAllocate;
                        $allocatedFees[] = [
                            'fee_id' => $fee->id,
                            'fee_type' => $fee->fee_type,
                            'amount_allocated' => $amountToAllocate,
                            'remaining_balance' => $fee->fresh()->balance
                        ];
                    }
                }
            }

            $this->updatePaymentStatus($payment, $totalAllocated);

            DB::commit();

            return [
                'success' => true,
                'allocated_amount' => $totalAllocated,
                'allocated_fees' => $allocatedFees,
                'message' => "Manually allocated KSh {$totalAllocated} to {$student->admission_number}"
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Manual allocation failed", [
                'payment_id' => $payment->id,
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'allocated_amount' => 0,
                'message' => 'Manual allocation failed: ' . $e->getMessage()
            ];
        }
    }
}
