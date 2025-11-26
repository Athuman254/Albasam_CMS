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
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('employment_type_id')->nullable();
            $table->unsignedBigInteger('language_id')->nullable();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('deadline_date');
            $table->longText('job_description');
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('language_id')->references('id')->on('languages');
            $table->foreign('employment_type_id')->references('id')->on('employment_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};
