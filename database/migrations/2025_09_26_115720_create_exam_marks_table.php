<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_marks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('exam_submission_id')->nullable();
            $table->unsignedBigInteger('exam_subject_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->unsignedBigInteger('exam_id')->nullable();

            $table->decimal('marks_obtained', 10, 2)->nullable();
            $table->decimal('maximum_marks', 10, 2)->nullable();
            $table->string('grade')->nullable();
            $table->string('status')->default('draft');
            $table->longText('remarks')->nullable();
            $table->longText('rejection_reason')->nullable();

            // Submission info
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();

            // Approval info
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Rejection info
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('exam_submission_id')->references('id')->on('exam_submissions')->onDelete('cascade');
            $table->foreign('exam_subject_id')->references('id')->on('exam_subjects')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('ranks')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index('status');
            $table->index('submitted_at');
            $table->index(['exam_id', 'class_id']);
            $table->index(['student_id', 'exam_subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_marks');
    }
};
