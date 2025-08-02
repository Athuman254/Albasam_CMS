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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('month');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('basic_salary')->nullable();
            $table->integer('total_allowances')->nullable();
            $table->integer('gross_salary')->nullable();
            $table->integer('tax_relief')->nullable();
            $table->integer('paye')->nullable();
            $table->integer('total_deductions')->nullable();
            $table->integer('net_salary')->nullable();
            $table->date('pay_date')->nullable();
            $table->integer('year')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->bigInteger('employee_type_id')->nullable();
            $table->string('job_title')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
