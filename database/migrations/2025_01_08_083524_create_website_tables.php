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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->string('title')->nullable();
            $table->string('sub_title')->nullable();
            $table->integer('order')->default(1); // Determines the display order
            $table->string('bg_style')->nullable();
            $table->string('bg_color')->nullable();
            $table->string('bg_image')->nullable();
            $table->tinyInteger('type')->default(0); // ['text' => 0, 'image' => 1, 'video' => 2,]
            $table->text('content')->nullable(); // Content for the section
            $table->string('type_image')->nullable(); // Content for the section
            $table->boolean('is_active')->default(true); // Controls visibility
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
        Schema::dropIfExists('sections');
        Schema::dropIfExists('pages');
    }
};
