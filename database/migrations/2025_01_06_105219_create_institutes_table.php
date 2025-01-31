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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('genders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('religions', function (Blueprint $table) {       // e.g. Christian, Islam, Hindu,
            $table->id();
            $table->string('name');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('relationships', function (Blueprint $table) {       // e.g. Father, Mother, Brother, Sister, Husband
            $table->id();
            $table->string('name');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('marital_statuses', function (Blueprint $table) {    //e.g. Single, Married, Single-Parent
            $table->id();
            $table->string('name');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('honorifics', function (Blueprint $table) {      // e.g. Mr, Mrs, Dr.,
            $table->id();
            $table->string('name');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employment_types', function (Blueprint $table) {        // e.g. Full-Time, Part-Time, Contract
            $table->id();
            $table->string('name');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employment_statuses', function (Blueprint $table) {     // e.g. Active, On-Leave, Retired, Resigned
            $table->id();
            $table->string('name');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('job_titles', function (Blueprint $table) {      // e.g. Teacher, Head Of Department
            $table->id();
            $table->string('name');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('specialization_areas', function (Blueprint $table) {    // e.g. STEM, Arts, Science
            $table->id();
            $table->string('name');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('qualification_types', function (Blueprint $table) {     // e.g. Degree, Diploma, Certificate
            $table->id();
            $table->string('name');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('salary_grades', function (Blueprint $table) {     // e.g. D5, D4, C1, B5
            $table->id();
            $table->string('name')->unique();
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('salary_scales', function (Blueprint $table) {     // e.g. T-Scale 15, T-Scale 14, T-Scale 13
            $table->id();
            $table->string('name')->unique();
            $table->unsignedBigInteger('salary_grade_id');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('salary_grade_id')->references('id')->on('salary_grades');

            $table->index('salary_grade_id');
        });

        Schema::create('teacher_titles', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();                            // e.g Principal, Deputy Principal
            $table->unsignedBigInteger('salary_grade_id');
            $table->unsignedBigInteger('salary_scale_id')->nullable();
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('salary_grade_id')->references('id')->on('salary_grades');
            $table->foreign('salary_scale_id')->references('id')->on('salary_scales');

            $table->index('salary_grade_id');
            $table->index('salary_scale_id');
        });

        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email');
            $table->string('phone');
            $table->string('country');
            $table->string('state')->nullable();
            $table->string('city');
            $table->text('physical_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('tax_identification_pin')->nullable();
            $table->longText('mission')->nullable();
            $table->longText('vision')->nullable();
            $table->string('x_profile')->nullable();
            $table->string('fb_profile')->nullable();
            $table->string('ig_profile')->nullable();
            $table->string('tiktok_profile')->nullable();
            $table->string('youtube_profile')->nullable();
            $table->integer('logo_size')->default(200);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genders');
        Schema::dropIfExists('religions');
        Schema::dropIfExists('relationships');
        Schema::dropIfExists('marital_statuses');
        Schema::dropIfExists('honorifics');
        Schema::dropIfExists('relationships');
        Schema::dropIfExists('employment_types');
        Schema::dropIfExists('employment_statuses');
        Schema::dropIfExists('job_titles');
        Schema::dropIfExists('specialization_areas');
        Schema::dropIfExists('qualification_types');
        Schema::dropIfExists('salary_grades');
        Schema::dropIfExists('salary_scales');
        Schema::dropIfExists('teacher_titles');
        Schema::dropIfExists('institutions');
    }
};
