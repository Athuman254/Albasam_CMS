<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_class', function (Blueprint $table) {
            $table->id();
            
            // Teacher reference
            $table->foreignId('teacher_id')
                  ->constrained('teachers')
                  ->onDelete('cascade');
            
            // Class reference (using ranks table for classes)
            $table->foreignId('class_id')
                  ->constrained('ranks')
                  ->onDelete('cascade');
            
            // Subject reference (optional)
            $table->foreignId('subject_id')
                  ->nullable()
                  ->constrained('subjects')
                  ->onDelete('cascade');
            
            // Academic year reference
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->onDelete('cascade');
            
            // Additional metadata
            $table->boolean('is_class_teacher')->default(false);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate assignments
            $table->unique(['teacher_id', 'class_id', 'subject_id', 'academic_year_id'], 'teacher_class_subject_year_unique');
            
            // Indexes for better performance
            $table->index(['teacher_id', 'academic_year_id']);
            $table->index(['class_id', 'academic_year_id']);
            $table->index(['subject_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_class');
    }
};
