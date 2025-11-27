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
        Schema::create('timetable_constraints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->enum('constraint_type', [
                'teacher_availability',
                'room_availability',
                'subject_preference',
                'class_capacity',
                'consecutive_periods',
                'no_double_booking'
            ]);
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('ranks')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('timetable_rooms')->onDelete('cascade');
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])->nullable();
            $table->integer('period_number')->nullable();
            $table->enum('constraint_value', ['available', 'unavailable', 'preferred', 'not_preferred', 'required']);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['academic_year_id', 'constraint_type']);
            $table->index(['teacher_id']);
            $table->index(['class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_constraints');
    }
};
