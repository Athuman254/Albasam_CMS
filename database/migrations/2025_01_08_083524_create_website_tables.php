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
            $table->foreignId('parent_id')->nullable()->constrained('sections')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('content')->nullable(); // Content for the section
            $table->tinyInteger('type')->default(0); // ['text' => 0, 'image' => 1, 'video' => 2,]
            $table->integer('order')->default(1); // Determines the display order
            $table->boolean('is_active')->default(true); // Controls visibility
            $table->timestamps();

            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');

            $table->index('page_id');
            $table->index('parent_id');
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
