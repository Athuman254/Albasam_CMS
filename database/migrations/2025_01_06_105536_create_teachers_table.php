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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('staff_number')->unique()->nullable();
            $table->date('date_of_hire')->unique()->nullable();
            $table->boolean('use_existing_user')->default(false);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('employment_type_id')->nullable();
            $table->unsignedBigInteger('employment_status_id')->nullable();
            $table->unsignedBigInteger('honorific_id')->nullable();
            $table->unsignedBigInteger('marital_status_id')->nullable();
            $table->unsignedBigInteger('gender_id');
            $table->unsignedBigInteger('religion_id');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('primary_phone')->unique();
            $table->string('secondary_phone')->nullable();
            $table->string('permanent_physical_address')->nullable();
            $table->string('secondary_physical_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('identification_number')->nullable();
            $table->string('tax_identification_pin')->nullable();
            $table->boolean('has_system_access')->default(false);
            $table->boolean('in_payroll')->default(false);
            $table->boolean('pays_paye')->default(false);
            $table->boolean('pays_sha')->default(false);
            $table->string('sha_no')->nullable();
            $table->boolean('pays_nssf')->nullable();
            $table->string('nssf_no')->nullable();
            $table->boolean('pays_housing_levy')->default(false);
            $table->string('password')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('employment_type_id')->references('id')->on('employment_types');
            $table->foreign('employment_status_id')->references('id')->on('employment_statuses');
            $table->foreign('honorific_id')->references('id')->on('honorifics');
            $table->foreign('marital_status_id')->references('id')->on('marital_statuses');
            $table->foreign('gender_id')->references('id')->on('genders');
            $table->foreign('religion_id')->references('id')->on('religions');

            $table->index('user_id');
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->unsignedBigInteger('honorific_id')->nullable();
            $table->unsignedBigInteger('job_title_id')->nullable();
            $table->unsignedBigInteger('specialization_area_id')->nullable();
            $table->string('tsc_number')->nullable();
            $table->tinyInteger('years_of_experience')->default(0)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('honorific_id')->references('id')->on('honorifics');
            $table->foreign('job_title_id')->references('id')->on('job_titles');
            $table->foreign('specialization_area_id')->references('id')->on('specialization_areas');

            $table->index('user_id');
            $table->index('employee_id');
            $table->index('honorific_id');
            $table->index('job_title_id');
            $table->index('specialization_area_id');
        });

        Schema::create('qualifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('qualification_type_id')->nullable();
            $table->string('institution_name');
            $table->string('course_name');
            $table->string('year_of_completion')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('qualification_type_id')->references('id')->on('qualification_types');
        });

        Schema::create('work_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('institution_name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('year_of_completion')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees');
        });

        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('relationship_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('relationship_id')->references('id')->on('relationships');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
        Schema::dropIfExists('work_histories');
        Schema::dropIfExists('qualifications');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('employees');
    }
};
