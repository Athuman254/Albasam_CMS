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
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->id();
            $table->string('seo_able_type'); // e.g. App\Models\Page, App\Models\Post
            $table->unsignedBigInteger('seo_able_id');
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->longText('keywords')->nullable();
            $table->timestamps();
            
            $table->index(['seo_able_type', 'seo_able_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
