<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AutoRecordedPayment;
use App\Models\Student;
use App\Models\Fee;
use App\Models\FeePayment;
use Illuminate\Support\Facades\Log;
use App\Services\MessageService;

class AllocateFeeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:allocate-fee-command {payment_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allocates Mpesa Payment To Student Fees';

    /**
     * Execute the console command.
     */
    public function handle(MessageService $messageService)
    {
        $paymentId = $this->argument('payment_id');

        Log::info("AllocateFeeCommand started", ['payment_id' => $paymentId]);

        // Find payment record with validation
        $paymentRecord = AutoRecordedPayment::find($paymentId);

        if (!$paymentRecord) {
            $this->error("Payment record not found with ID: {$paymentId}");
            Log::error("Payment record not found", ['payment_id' => $paymentId]);
            return 1;
        }

        Log::info("Payment record found", [
            'payment_id' => $paymentRecord->id,
            'amount' => $paymentRecord->amount,
            'reference' => $paymentRecord->reference_number,
            'matched_student_id' => $paymentRecord->matched_student_id,
            'matched_admission_number' => $paymentRecord->matched_admission_number
        ]);

        // Use Student model instead of StudentAdmission
        $student = Student::find($paymentRecord->matched_student_id);

        if (!$student) {
            $this->error("Student not found for payment ID: {$paymentRecord->id}");
            Log::error("Student not found", [
                'payment_id' => $paymentRecord->id,
                'matched_student_id' => $paymentRecord->matched_student_id
            ]);
            return 1;
        }

        Log::info("Student found", [
            'student_id' => $student->id,
            'admission_number' => $student->admission_number,
            'name' => $student->full_name
        ]);

        // Get unpaid fees for the student
        $unpaidFees = Fee::where('student_id', $student->id)
            ->where('balance', '>', 0)
            ->orderBy('academic_year', 'asc')
            ->orderBy('term', 'asc')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->with(['rank'])
            ->get();

        Log::info("Unpaid fees retrieved", [
            'student_id' => $student->id,
            'unpaid_fees_count' => $unpaidFees->count(),
            'total_balance' => $unpaidFees->sum('balance')
        ]);

        $amountPaid = $paymentRecord->amount;
        $totalAllocated = 0;
        $allocatedFees = [];

        foreach ($unpaidFees as $fee) {
            if ($amountPaid <= 0) {
                break;
            }

            $feeBalance = $fee->balance;
            $amountToAllocate = min($feeBalance, $amountPaid);

            if ($amountToAllocate > 0) {
                // Create FeePayment record
                try {
                    FeePayment::create([
                        'fee_id' => $fee->id,
                        'student_id' => $student->id,
                        'amount' => $amountToAllocate,
                        'payment_method' => 'mpesa',
                        'reference_number' => $paymentRecord->reference_number,
                        'payment_date' => now(),
                        'status' => 'completed',
                        'auto_recorded_payment_id' => $paymentRecord->id,
                        'notes' => 'Auto-allocated from M-Pesa payment'
                    ]);

                    Log::info("FeePayment record created", [
                        'fee_id' => $fee->id,
                        'amount_allocated' => $amountToAllocate
                    ]);

                    // Update fee balance and status
                    $newBalance = $feeBalance - $amountToAllocate;
                    $newPaidAmount = $fee->paid_amount + $amountToAllocate;

                    $fee->update([
                        'paid_amount' => $newPaidAmount,
                        'balance' => $newBalance,
                        'status' => $newBalance <= 0 ? 'paid' : 'partial'
                    ]);

                    Log::info("Fee record updated", [
                        'fee_id' => $fee->id,
                        'new_balance' => $newBalance,
                        'new_status' => $newBalance <= 0 ? 'paid' : 'partial'
                    ]);

                    $amountPaid -= $amountToAllocate;
                    $totalAllocated += $amountToAllocate;

                    $allocatedFees[] = [
                        'fee_id' => $fee->id,
                        'amount_allocated' => $amountToAllocate,
                        'remaining_balance' => $newBalance
                    ];
                } catch (\Exception $e) {
                    Log::error("Failed to allocate fee payment", [
                        'fee_id' => $fee->id,
                        'error' => $e->getMessage()
                    ]);
                    $this->error("Failed to allocate fee payment for fee ID: {$fee->id}");
                }

                if ($amountPaid <= 0) {
                    break;
                }
            }
        }

        // Update payment status
        $paymentStatus = $amountPaid > 0 ? 'partially_allocated' : 'fully_allocated';

        $paymentRecord->update([
            'status' => $paymentStatus,
            'allocated_at' => now(),
            'allocation_notes' => "Allocated KSh {$totalAllocated} to " . count($allocatedFees) . " fee items"
        ]);

        // Send SMS Receipt
        try {
            $messageService->sendPaymentReceipt($student, $totalAllocated, $paymentRecord->reference_number);
        } catch (\Exception $e) {
            Log::error("SMS Receipt error in AllocateFeeCommand: " . $e->getMessage());
        }

        Log::info("Payment allocation completed", [
            'payment_id' => $paymentRecord->id,
            'total_allocated' => $totalAllocated,
            'remaining_amount' => $amountPaid,
            'allocated_fees_count' => count($allocatedFees),
            'payment_status' => $paymentStatus
        ]);

        $this->info("Successfully allocated KSh {$totalAllocated} to {$student->admission_number}");
        $this->info("Remaining amount: KSh {$amountPaid}");
        $this->info("Allocated to " . count($allocatedFees) . " fee items");

        return 0;
    }
}
