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
        Schema::create('timetable_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_name'); // "Lab 1", "Class 3A", "Library"
            $table->enum('room_type', ['classroom', 'laboratory', 'library', 'hall', 'sports', 'other'])->default('classroom');
            $table->integer('capacity')->default(40);
            $table->json('facilities')->nullable(); // ["projector", "computers", "whiteboard"]
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->enum('status', ['available', 'maintenance', 'reserved'])->default('available');
            $table->timestamps();

            // Indexes
            $table->index(['status']);
            $table->index(['room_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_rooms');
    }
};
