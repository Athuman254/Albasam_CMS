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
        Schema::create('student_admissions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('admission_number')->unique();
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('rank_id');
            $table->text('physical_disability')->nullable();
            $table->text('hobby')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('division_id')->references('id')->on('divisions');
            $table->foreign('rank_id')->references('id')->on('ranks');
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_admission_id');
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->unsignedBigInteger('religion_id')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('date_of_birth');
            $table->string('birth_certificate_number')->nullable();
            $table->string('citizenship')->nullable();
            $table->string('county')->nullable();
            $table->string('ward')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('kpsea_score')->nullable();
            $table->string('kjsea_score')->nullable();
            $table->string('kcpe_score')->nullable();
            $table->string('upi_number')->nullable();
            $table->string('index_number')->nullable();
            $table->string('nemis')->nullable();
            $table->string('assessment_number')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('specialization')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_admission_id')->references('id')->on('student_admissions');
            $table->foreign('gender_id')->references('id')->on('genders');
            $table->foreign('religion_id')->references('id')->on('religions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('student_admissions');
    }
};
