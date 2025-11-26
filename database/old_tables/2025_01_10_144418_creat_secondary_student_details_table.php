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
        Schema::create('secondary_student_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->unique(); // Links to students table
            $table->string('kcpe_marks')->nullable();
            $table->string('index_number')->nullable();
            $table->string('nemis')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('specialization')->nullable(); // E.g., Sciences, Arts
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
        
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->tinyInteger('type');
            $table->string('sub_title');
            $table->string('title');
            $table->longText('details')->nullable();
            $table->tinyInteger('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
            
            $table->index('page_id');
        });
        
        Schema::create('sub_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->string('title')->nullable();
            $table->string('sub_title')->nullable();
            $table->integer('order')->default(1); // Determines the display order
            $table->tinyInteger('type')->default(1); // ['text' => 1, 'image' => 2, 'video' => 3,]
            $table->text('content')->nullable(); // Content for the section
            $table->string('type_image')->nullable(); // Content for the section
            $table->boolean('is_active')->default(true); // Controls visibility
            $table->timestamps();
            
            $table->foreign('section_id')->references('id')->on('pages')->onDelete('cascade');
            
            $table->index('section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secondary_student_details');
    }
};
