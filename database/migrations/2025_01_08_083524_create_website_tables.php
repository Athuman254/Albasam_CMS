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
        Schema::create('logos', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('image_src',200);
            $table->string('image_alt', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('transition_type', 50)->nullable();
            $table->string('image_src',200)->nullable();
            $table->string('caption_title')->nullable();
            $table->string('caption')->nullable();
            $table->string('vedio_url')->nullable();
            $table->string('image_position', 50)->nullable();
            $table->boolean('has_overlay')->default(false);
            $table->integer('order_position')->nullable();
            $table->timestamps();
        });

        Schema::create('slide_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slide_id')->constrained('slides')->onDelete('cascade');
            $table->string('layer_type', 50);
            $table->text('content')->nullable();
            $table->string('position_x', 50)->nullable();
            $table->string('position_y', 50)->nullable();
            $table->string('hoffset', 50)->nullable();
            $table->string('voffset', 50)->nullable();
            $table->string('width', 20)->nullable();
            $table->string('height', 20)->nullable();
            $table->string('whitespace', 20)->default('normal');
            $table->integer('start_delay')->nullable();
            $table->string('split_in', 20)->nullable();
            $table->string('split_out', 20)->nullable();
            $table->string('responsive_offset', 10)->nullable();
            $table->string('padding_right', 50)->nullable();
            $table->string('padding_top', 50)->nullable();
            $table->string('padding_bottom', 50)->nullable();
            $table->integer('order_position');
            $table->timestamps();
        });

        Schema::create('layer_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layer_id')->constrained('slide_layers')->onDelete('cascade');
            $table->string('content_type', 50);
            $table->text('label')->nullable();
            $table->string('url')->nullable();
            $table->string('icon_class', 50)->nullable();
            $table->string('css_class', 100)->nullable();
            $table->integer('order_position');
            $table->timestamps();
        });

        Schema::create('carousel_items', function (Blueprint $table) {
            $table->id();
            $table->string('image_src',200);
            $table->string('image_alt', 100)->nullable();
            $table->integer('order_position');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('quote_contents', function (Blueprint $table) {
            $table->id();
            $table->string('image_src',200)->nullable();
            $table->string('image_alt', 100)->nullable();
            $table->string('title')->nullable();
            $table->string('title_class', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('button_text', 50)->nullable();
            $table->string('button_url')->nullable();
            $table->timestamps();
        });

        Schema::create('about_contents', function (Blueprint $table) {
            $table->id();
            $table->string('image_src')->nullable();
            $table->string('image_alt', 200)->nullable();
            $table->string('video_src')->nullable();
            $table->string('title_section', 50)->nullable();
            $table->string('title', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('stmt1', 100)->nullable();
            $table->string('stmt2', 100)->nullable();
            $table->string('stmt3', 100)->nullable();
            $table->string('stmt4', 100)->nullable();
            $table->string('button_text', 50)->nullable();
            $table->string('button_url')->nullable();
            $table->timestamps();
        });

        Schema::create('why_choose_us_contents', function (Blueprint $table) {
            $table->id();
            $table->string('image_src')->nullable();
            $table->string('image_alt', 200)->nullable();
            $table->string('title', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('testimony_authors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('image_src',255)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::create('upcoming_events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('venue', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('quick_links', function (Blueprint $table) {
            $table->id();
            $table->string('welcome_text', 100)->nullable();
            $table->string('title', 100);
            $table->date('date');
            $table->string('button_label', 100)->nullable();
            $table->string('button_url', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('link1_text', 20)->nullable();
            $table->string('link1_href', 255)->nullable();
            $table->string('link2_text', 20)->nullable();
            $table->string('link2_href', 255)->nullable();
            $table->string('link3_text', 20)->nullable();
            $table->string('link3_href', 255)->nullable();
            $table->string('link4_text', 20)->nullable();
            $table->string('link4_href', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quick_links');
        Schema::dropIfExists('upcoming_events');
        Schema::dropIfExists('testimony_authors');
        Schema::dropIfExists('why_choose_us_contents');
        Schema::dropIfExists('about_contents');
        Schema::dropIfExists('quote_contents');
        Schema::dropIfExists('carousel_items');
        Schema::dropIfExists('layer_contents');
        Schema::dropIfExists('slide_layers');
        Schema::dropIfExists('slides');
        Schema::dropIfExists('logos');
    }
};
