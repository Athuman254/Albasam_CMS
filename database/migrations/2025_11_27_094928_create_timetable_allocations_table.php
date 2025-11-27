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
        Schema::create('timetable_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('timetable_versions')->onDelete('cascade');
            $table->foreignId('period_id')->constrained('timetable_periods')->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->foreignId('class_id')->constrained('ranks')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('timetable_rooms')->onDelete('set null');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
            $table->integer('period_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->boolean('is_substitution')->default(false); // For teacher substitutions
            $table->foreignId('original_teacher_id')->nullable()->constrained('users')->onDelete('set null'); // If substituted
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['version_id', 'class_id', 'day_of_week']);
            $table->index(['version_id', 'teacher_id', 'day_of_week']);
            $table->index(['version_id', 'room_id', 'day_of_week']);
            $table->index(['academic_year_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_allocations');
    }
};
