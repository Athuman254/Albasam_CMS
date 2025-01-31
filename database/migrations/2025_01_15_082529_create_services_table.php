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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('discription')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image',255)->nullable();
            $table->string('temp_image',255)->nullable();
            $table->string('service_includes',255)->nullable();
            $table->bigInteger('price')->unsigned()->nullable();
            $table->string('vedio_src')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('has_started')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
