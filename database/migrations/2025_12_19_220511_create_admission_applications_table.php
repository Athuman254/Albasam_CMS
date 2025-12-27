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
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();

            // Student Information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->foreignId('gender_id')->constrained()->onDelete('cascade');
            $table->string('birth_certificate_number')->nullable();
            $table->foreignId('religion_id')->nullable()->constrained()->onDelete('set null');

            // Class/Grade Information
            $table->foreignId('rank_id')->constrained('ranks')->onDelete('cascade');
            $table->foreignId('division_id')->nullable()->constrained()->onDelete('set null');
            $table->string('academic_year');

            // Guardian Information
            $table->string('guardian_name');
            $table->string('guardian_email');
            $table->string('guardian_phone');
            $table->foreignId('relationship_id')->constrained()->onDelete('cascade');
            $table->text('guardian_address')->nullable();

            // Previous School (optional)
            $table->string('previous_school')->nullable();
            $table->string('previous_class')->nullable();

            // Application Status
            $table->string('status')->default('pending'); // pending, reviewed, approved, rejected
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('reviewed_at')->nullable();

            // Student record (if approved and created)
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('set null');

            // Tracking
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
