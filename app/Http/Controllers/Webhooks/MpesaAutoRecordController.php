<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\AutoRecordedPayment;
use App\Models\Student;
use App\Services\PaymentAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class MpesaAutoRecordController extends Controller
{
    protected $paymentAllocationService;

    public function __construct(PaymentAllocationService $paymentAllocationService)
    {
        $this->paymentAllocationService = $paymentAllocationService;
    }

    public function handlePayment(Request $request)
    {
        $data = $request->all();

        Log::info('MPesa Webhook Received:', $data);

        $transactionId = $data['TransID'] ?? null;
        $amount = $data['TransAmount'] ?? null;
        $phone = $data['MSISDN'] ?? null;
        $payerName = $data['FirstName'] ?? 'Unknown';
        $accountNumber = $data['BillRefNumber'] ?? null; 
        $businessShortCode = $data['BusinessShortCode'] ?? null;

        if (!$transactionId || !$amount) {
            Log::warning('Invalid MPesa webhook data received', ['data' => $data]);
            return response()->json(['ResultCode' => '1', 'ResultDesc' => 'Invalid data']);
        }

        $yourPaybill = config('services.mpesa.paybill_number');
        if ($businessShortCode && $businessShortCode != $yourPaybill) {
            Log::warning('MPesa payment for wrong paybill', [
                'received' => $businessShortCode, 
                'expected' => $yourPaybill
            ]);
            return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Success']);
        }

        $existing = AutoRecordedPayment::where('reference_number', $transactionId)->first();
        if ($existing) {
            Log::info('MPesa payment already exists', ['transaction_id' => $transactionId]);
            return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Success']);
        }

        $student = null;
        $status = 'unmatched';
        
        if ($accountNumber) {
            // Use Student model to find by admission number
            $student = Student::where('admission_number', $accountNumber)->first();
            
            if ($student) {
                // Generate fees for student if they don't exist
                $this->paymentAllocationService->generateStudentFees($student);
                
                $status = 'recorded';
                
                Log::info("MPesa payment auto-matched to student", [
                    'admission_number' => $accountNumber,
                    'student_id' => $student->id,
                    'transaction_id' => $transactionId,
                    'student_name' => $student->full_name
                ]);

                // Create and auto-allocate payment
                $payment = AutoRecordedPayment::create([
                    'reference_number' => $transactionId,
                    'amount' => $amount,
                    'payment_method' => 'mpesa',
                    'account_number' => $businessShortCode ?? config('services.mpesa.paybill_number'),
                    'payer_name' => $payerName,
                    'payer_phone' => $phone,
                    'payment_date' => now(),
                    'narration' => 'MPesa Payment - ' . ($accountNumber ?: 'No account provided'),
                    'matched_admission_number' => $accountNumber,
                    'matched_student_id' => $student->id,
                    'status' => $status,
                ]);

                Log::info("AutoRecordedPayment created", [
                    'payment_id' => $payment->id,
                    'student_id' => $student->id
                ]);

                // Execute allocation command with error handling
                try {
                    $exitCode = Artisan::call('app:allocate-fee-command', [
                        'payment_id' => $payment->id
                    ]);
                    
                    if ($exitCode !== 0) {
                        Log::error("Fee allocation command failed with exit code", [
                            'payment_id' => $payment->id,
                            'exit_code' => $exitCode
                        ]);
                    } else {
                        Log::info("Fee allocation command executed successfully", [
                            'payment_id' => $payment->id
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    Log::error("Failed to execute allocation command", [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

            } else {
                Log::warning("MPesa payment with unknown admission number", [
                    'admission_number' => $accountNumber,
                    'transaction_id' => $transactionId
                ]);
                
                // Record as unmatched but with admission number
                AutoRecordedPayment::create([
                    'reference_number' => $transactionId,
                    'amount' => $amount,
                    'payment_method' => 'mpesa',
                    'account_number' => $businessShortCode ?? config('services.mpesa.paybill_number'),
                    'payer_name' => $payerName,
                    'payer_phone' => $phone,
                    'payment_date' => now(),
                    'narration' => 'MPesa Payment - ' . ($accountNumber ?: 'No account provided'),
                    'matched_admission_number' => $accountNumber,
                    'status' => 'unmatched',
                ]);
            }
        } else {
            Log::warning("MPesa payment without admission number in BillRefNumber", [
                'transaction_id' => $transactionId,
                'payer_name' => $payerName
            ]);
            
            // Record as completely unmatched
            AutoRecordedPayment::create([
                'reference_number' => $transactionId,
                'amount' => $amount,
                'payment_method' => 'mpesa',
                'account_number' => $businessShortCode ?? config('services.mpesa.paybill_number'),
                'payer_name' => $payerName,
                'payer_phone' => $phone,
                'payment_date' => now(),
                'narration' => 'MPesa Payment - No account provided',
                'status' => 'unmatched',
            ]);
        }

        Log::info("MPesa payment processing completed", ['transaction_id' => $transactionId]);
        return response()->json(['ResultCode' => '0', 'ResultDesc' => 'Success']);
    }
}