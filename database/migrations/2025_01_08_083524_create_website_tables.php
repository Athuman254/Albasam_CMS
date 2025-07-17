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
            $table->string('type');
            $table->string('sub_title');
            $table->string('title');
            $table->longText('details')->nullable();
            $table->string('component_type')->nullable();
            $table->tinyInteger('order')->default(0);
            $table->boolean('section_has_image')->default(true);
            $table->boolean('include_contact_cards')->default(false);
            $table->boolean('section_image_first')->default(true);
            $table->boolean('has_cta_buttons')->default(true);
            $table->boolean('active')->default(true);
            $table->string('map_link')->nullable();
            $table->timestamps();
            
            $table->foreign('page_id')->references('id')->on('pages');

            $table->index('page_id');
        });
        
        Schema::create('section_cta_buttons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('page_id');
            $table->string('cta_button_text');
            $table->string('cta_button_type')->default('primary-btn');
            $table->timestamps();
            
            $table->foreign('section_id')->references('id')->on('sections');
            $table->foreign('page_id')->references('id')->on('pages');
        });
        
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->string('title');
            $table->string('type');     // e.g page, custom, service
            $table->string('url')->nullable();
            $table->boolean('has_children')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('menus');
            $table->string('child_type')->nullable();
            $table->string('component')->nullable();
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
