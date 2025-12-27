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
        Schema::create('exam_skill_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_mark_id')->constrained('exam_marks')->onDelete('cascade');
            $table->foreignId('exam_subject_skill_id')->constrained('exam_subject_skills')->onDelete('cascade');
            $table->decimal('marks_obtained', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_skill_marks');
    }
};
