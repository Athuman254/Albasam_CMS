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
       
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable();
            $table->tinyInteger('group');
            $table->boolean('activated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('rank_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rank_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->timestamps();
            
            $table->foreign('rank_id')->references('id')->on('ranks');
            $table->foreign('subject_id')->references('id')->on('subjects');
            $table->foreign('teacher_id')->references('id')->on('teachers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rank_subjects');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('ranks');
    }
};
