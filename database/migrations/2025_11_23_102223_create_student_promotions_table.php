<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_class_id')->constrained('ranks')->onDelete('cascade');
            $table->foreignId('to_class_id')->constrained('ranks')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('promoted_by')->constrained('employees')->onDelete('cascade');
            $table->boolean('special_promotion')->default(false);
            $table->text('reason')->nullable();
            $table->boolean('has_completed_all_terms')->default(false);
            $table->json('completed_terms')->nullable();
            $table->timestamp('promoted_at');
            $table->timestamps();

            // Prevent duplicate promotions in same academic year
            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_promotions');
    }
};