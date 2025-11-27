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
        Schema::create('timetable_subject_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('ranks')->onDelete('cascade'); // ranks table is classes
            $table->integer('hours_per_week')->default(5); // Required teaching hours per week
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Prevent duplicate allocations
            $table->unique(['academic_year_id', 'teacher_id', 'subject_id', 'class_id'], 'unique_allocation');

            // Indexes for performance
            $table->index(['academic_year_id', 'teacher_id']);
            $table->index(['academic_year_id', 'class_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_subject_allocations');
    }
};
