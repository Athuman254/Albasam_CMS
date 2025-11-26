<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('rank_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->string('academic_year');
            $table->string('term');
            $table->date('due_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2);
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->boolean('is_carry_over')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['student_id', 'academic_year', 'term']);
            $table->index(['due_date', 'status']);
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_invoices');
    }
};