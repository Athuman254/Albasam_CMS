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
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date');

            // Clock In Information
            $table->dateTime('clock_in_time');
            $table->decimal('clock_in_latitude', 10, 8)->nullable();
            $table->decimal('clock_in_longitude', 11, 8)->nullable();

            // Clock Out Information
            $table->dateTime('clock_out_time')->nullable();
            $table->decimal('clock_out_latitude', 10, 8)->nullable();
            $table->decimal('clock_out_longitude', 11, 8)->nullable();

            // Status and Notes
            $table->enum('status', ['present', 'late', 'half_day', 'absent'])->default('present');
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['employee_id', 'date']);
            $table->index('date');
            $table->index('status');

            // Ensure one attendance record per employee per day
            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
