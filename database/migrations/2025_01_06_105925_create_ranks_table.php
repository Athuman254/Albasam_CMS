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
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('stream_id')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('division_id')->references('id')->on('divisions');
            $table->foreign('stream_id')->references('id')->on('streams');
            $table->foreign('teacher_id')->references('id')->on('teachers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
