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
        Schema::create('siblings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('name');
            $table->tinyInteger('age')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->string('current_school')->nullable();
            $table->string('current_class')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('gender_id')->references('id')->on('genders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siblings');
    }
};
