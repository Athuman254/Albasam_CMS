<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AutoRecordedPayment;
use App\Models\Student;
use App\Services\PaymentAllocationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutoImportBankPayments extends Command
{
    protected $signature = 'payments:import-bank';
    protected $description = 'Automatically import bank payments from bank API';

    protected $paymentAllocationService;

    public function __construct(PaymentAllocationService $paymentAllocationService)
    {
        parent::__construct();
        $this->paymentAllocationService = $paymentAllocationService;
    }

    public function handle()
    {
        $this->info('Starting bank payments import...');

        // Get your bank account details from config
        $accountNumber = config('services.bank.account_number');
        $apiKey = config('services.bank.api_key');
        $apiUrl = config('services.bank.api_url');

        if (!$accountNumber || !$apiKey) {
            $this->error('Bank API credentials not configured');
            Log::error('Bank API credentials not configured');
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->get($apiUrl, [
                'account_number' => $accountNumber,
                'from_date' => now()->subDays(1)->format('Y-m-d'), 
                'to_date' => now()->format('Y-m-d'),
            ]);

            if (!$response->successful()) {
                $this->error('Bank API request failed: ' . $response->status());
                Log::error('Bank API request failed', ['status' => $response->status(), 'response' => $response->body()]);
                return;
            }

            $transactions = $response->json();

            if (empty($transactions)) {
                $this->info('No new transactions found');
                return;
            }

            $importedCount = 0;
            $allocatedCount = 0;
            $skippedCount = 0;

            foreach ($transactions as $transaction) {
                // Check if already exists
                $existing = AutoRecordedPayment::where('reference_number', $transaction['reference'])->first();
                if ($existing) {
                    $skippedCount++;
                    continue;
                }

                // Extract student admission number from narration
                $admissionNumber = $this->extractAdmissionNumber($transaction['narration']);
                $student = $admissionNumber ? Student::where('admission_number', $admissionNumber)->first() : null;

                $payment = AutoRecordedPayment::create([
                    'reference_number' => $transaction['reference'],
                    'transaction_id' => $transaction['id'] ?? null,
                    'amount' => $transaction['amount'],
                    'payment_method' => 'bank',
                    'account_number' => $accountNumber,
                    'payer_name' => $transaction['payer_name'] ?? 'Unknown',
                    'payer_account' => $transaction['payer_account'] ?? null,
                    'payment_date' => $transaction['date'] ?? now(),
                    'narration' => $transaction['narration'],
                    'matched_admission_number' => $admissionNumber,
                    'matched_student_id' => $student?->id,
                    'status' => $student ? 'recorded' : 'unmatched',
                ]);

                $importedCount++;

                // Auto-allocate if student found
                if ($student) {
                    $this->paymentAllocationService->generateStudentFees($student);
                    
                    try {
                        $allocationResult = $this->paymentAllocationService->allocatePaymentToStudent($payment, $student);
                        $allocatedCount++;
                        
                        $this->info("Allocated payment for student {$admissionNumber}: KSh {$transaction['amount']}");
                    } catch (\Exception $e) {
                        $this->error("Failed to allocate payment for {$admissionNumber}: " . $e->getMessage());
                    }
                } else {
                    $this->warn("Imported unmatched payment: KSh {$transaction['amount']} - {$transaction['narration']}");
                }
            }

            // Process any remaining unmatched payments
            $processedUnmatched = $this->paymentAllocationService->processUnmatchedPayments();
            
            $this->info("Bank payments import completed: {$importedCount} imported, {$allocatedCount} allocated, {$processedUnmatched} unmatched processed, {$skippedCount} skipped");

            Log::info('Bank payments auto-import completed', [
                'imported_count' => $importedCount,
                'allocated_count' => $allocatedCount,
                'unmatched_processed' => $processedUnmatched,
                'skipped_count' => $skippedCount
            ]);

        } catch (\Exception $e) {
            $this->error('Bank import failed: ' . $e->getMessage());
            Log::error('Bank payments auto-import failed', [
                'error' => $e->getMessage(),
                'account_number' => $accountNumber
            ]);
        }
    }

    private function extractAdmissionNumber($narration)
    {
        if (empty($narration)) return null;

        $patterns = [
            '/(STU\d+)/i',                   
            '/(STU[\s\-_]?\d+)/i',            
            '/(ADM\d+)/i',                   
            '/(ADM[\s\-_]?\d+)/i',           
            '/(\d{4}[A-Z]?\d{2,3})/i',       
        ];

        foreach ($patterns as $pattern) {
            preg_match($pattern, $narration, $matches);
            if (!empty($matches[1])) {
                return strtoupper(trim($matches[1]));
            }
        }

        return null;
    }
}