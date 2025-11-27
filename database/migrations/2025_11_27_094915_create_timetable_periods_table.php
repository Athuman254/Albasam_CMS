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
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('period_name'); // "Period 1", "Morning Assembly", etc.
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_break')->default(false);
            $table->string('break_type')->nullable(); // "Short Break", "Lunch", "Assembly", "Games"
            $table->integer('duration_minutes');
            $table->integer('period_order')->default(0); // For sorting periods
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Indexes for performance
            $table->index(['academic_year_id', 'day_of_week', 'start_time']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_periods');
    }
};
