<?php
// database/migrations/2025_11_16_000000_create_fee_management_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fee Structures Table
        if (!Schema::hasTable('fee_structures')) {
            Schema::create('fee_structures', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rank_id')->constrained()->onDelete('cascade');
                $table->string('academic_year');
                $table->string('term'); // 1, 2, 3
                $table->decimal('amount', 10, 2);
                $table->text('description')->nullable();
                $table->date('due_date');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // Ensure unique fee structure per class, year, and term
                $table->unique(['rank_id', 'academic_year', 'term']);
                
                // Indexes for performance
                $table->index(['academic_year', 'term']);
                $table->index('is_active');
            });
        }

        // Fees Table
        if (!Schema::hasTable('fees')) {
            Schema::create('fees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained()->onDelete('cascade');
                $table->foreignId('rank_id')->constrained()->onDelete('cascade');
                $table->foreignId('original_fee_structure_id')->nullable()->constrained('fee_structures')->onDelete('set null');
                $table->decimal('amount', 10, 2);
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->decimal('balance', 10, 2);
                $table->string('academic_year');
                $table->string('term');
                $table->date('due_date');
                $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'carried_over'])->default('pending');
                $table->boolean('is_carry_over')->default(false);
                $table->text('description')->nullable();
                $table->timestamps();

                // Add indexes for better performance
                $table->index(['student_id', 'status']);
                $table->index(['academic_year', 'term']);
                $table->index('due_date');
                $table->index('status');
                $table->index('is_carry_over');
                $table->index(['student_id', 'academic_year', 'term']);
                $table->index('original_fee_structure_id');
            });
        }

        // Fee Payments Table
        if (!Schema::hasTable('fee_payments')) {
            Schema::create('fee_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fee_id')->constrained()->onDelete('cascade');
                $table->foreignId('student_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->string('payment_method'); // mpesa, bank, cash
                $table->string('reference_number'); // receipt_no, mpesa_code, cheque_no
                $table->string('transaction_id')->nullable();
                $table->date('payment_date');
                $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('pending');
                $table->text('notes')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users');
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                // Add indexes for better performance
                $table->index(['student_id', 'payment_date']);
                $table->index('reference_number');
                $table->index('payment_method');
                $table->index('status');
                $table->index('payment_date');
                $table->index(['fee_id', 'status']);
                $table->unique(['reference_number', 'status']); // Prevent duplicate completed payments
            });
        }

        // Fee Transfers Table
        if (!Schema::hasTable('fee_transfers')) {
            Schema::create('fee_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('from_student_id')->constrained('students')->onDelete('cascade');
                $table->foreignId('to_student_id')->constrained('students')->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->string('reason');
                $table->foreignId('initiated_by')->constrained('users');
                $table->timestamps();

                // Add indexes for better performance
                $table->index('from_student_id');
                $table->index('to_student_id');
                $table->index('initiated_by');
                $table->index('created_at');
            });
        }

        // =============================================
        // AUTO RECORDED PAYMENTS TABLE (NEW)
        // =============================================
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

        // =============================================
        // BANK STATEMENT IMPORTS TABLE (NEW)
        // =============================================
        if (!Schema::hasTable('bank_statement_imports')) {
            Schema::create('bank_statement_imports', function (Blueprint $table) {
                $table->id();
                $table->string('filename');
                $table->string('account_number');
                $table->string('payment_method'); 
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
        Schema::dropIfExists('fee_transfers');
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('fees');
        Schema::dropIfExists('fee_structures');
    }
};