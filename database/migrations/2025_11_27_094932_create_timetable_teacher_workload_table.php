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
        Schema::create('timetable_teacher_workload', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->integer('total_classes')->default(0);
            $table->integer('total_subjects')->default(0);
            $table->integer('total_hours_per_week')->default(0);
            $table->decimal('classes_per_day_avg', 5, 2)->default(0);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            // Unique constraint - one record per teacher per academic year
            $table->unique(['academic_year_id', 'teacher_id']);

            // Indexes
            $table->index(['academic_year_id']);
            $table->index(['teacher_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_teacher_workload');
    }
};
