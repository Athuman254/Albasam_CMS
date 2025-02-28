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
        Schema::create('junior_high_student_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->unique(); // Links to students table
            $table->string('kpsea_score')->nullable();
            $table->string('index_number')->nullable();
            $table->string('nemis')->nullable();
            $table->string('assessment_number')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('specialization')->nullable(); // E.g., Sciences, Arts
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('junior_high_student_details');
    }
};
