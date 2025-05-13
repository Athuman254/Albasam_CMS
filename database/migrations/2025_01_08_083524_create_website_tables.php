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
        Schema::create('customisations', function (Blueprint $table) {
            $table->id();
            $table->string('primary_color');
            $table->string('primary_color_rgb')->nullable();
            $table->string('primary_color_light')->nullable();
            $table->string('primary_color_light_rgb')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('secondary_color_rgb')->nullable();
            $table->string('secondary_color_light')->nullable();
            $table->string('secondary_color_light_rgb')->nullable();
            $table->string('button_style')->nullable();
            $table->timestamps();
        });
        
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('slug')->unique()->nullable();
            $table->longText('description')->nullable();
            $table->boolean('published')->default(false);
            $table->boolean('is_home')->default(false);
            $table->timestamps();
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
        
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->string('title');
            $table->string('type');     // e.g page, custom, service
            $table->string('url')->nullable();
            $table->boolean('has_children')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('menus');
            $table->tinyInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customisations');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('sub-sections');
        Schema::dropIfExists('menus');
    }
};
