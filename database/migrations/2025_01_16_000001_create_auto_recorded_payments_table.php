<?php
// database/migrations/2025_01_16_000001_create_auto_recorded_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('auto_recorded_payments')) {
            Schema::create('auto_recorded_payments', function (Blueprint $table) {
                $table->id();
                
                // Payment identification
                $table->string('reference_number')->unique(); // MPesa code or bank receipt
                $table->string('transaction_id')->nullable(); // Bank transaction ID
                
                // Payment details
                $table->decimal('amount', 10, 2);
                $table->string('payment_method'); // mpesa, bank, cheque
                $table->string('account_number'); // School bank account or MPesa paybill
                
                // Payer information
                $table->string('payer_name');
                $table->string('payer_phone')->nullable();
                $table->string('payer_account')->nullable(); // Payer's bank account/phone
                
                // Payment metadata
                $table->date('payment_date');
                $table->text('narration')->nullable(); // Payment description
                
                // Verification status
                $table->enum('status', ['recorded', 'verified', 'rejected', 'unmatched'])->default('recorded');
                
                // Student matching
                $table->foreignId('matched_student_id')->nullable()->constrained('students')->onDelete('cascade');
                $table->string('matched_admission_number')->nullable();
                
                // Verification details
                $table->text('verification_notes')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users');
                $table->timestamp('verified_at')->nullable();
                
                // Timestamps
                $table->timestamps();

                // Indexes for performance
                $table->index('reference_number');
                $table->index('transaction_id');
                $table->index('status');
                $table->index('payment_date');
                $table->index('payment_method');
                $table->index('matched_admission_number');
                $table->index('matched_student_id');
                $table->index('account_number');
                $table->index(['status', 'payment_date']);
                $table->index(['payment_method', 'status']);
            });
        }

        // Also add a related table for bank statement imports tracking
        if (!Schema::hasTable('bank_statement_imports')) {
            Schema::create('bank_statement_imports', function (Blueprint $table) {
                $table->id();
                $table->string('filename');
                $table->string('account_number');
                $table->string('payment_method'); // bank, mpesa
                $table->integer('records_imported')->default(0);
                $table->integer('records_skipped')->default(0);
                $table->text('import_notes')->nullable();
                $table->foreignId('imported_by')->constrained('users');
                $table->timestamps();

                $table->index('account_number');
                $table->index('payment_method');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statement_imports');
        Schema::dropIfExists('auto_recorded_payments');
    }
};