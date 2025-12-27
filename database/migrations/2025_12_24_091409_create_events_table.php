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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('event_type', ['holiday', 'exam', 'meeting', 'sports', 'academic', 'other'])->default('other');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('color')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('target_audience', ['all', 'students', 'teachers', 'parents', 'specific_class'])->default('all');
            $table->foreignId('rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_pattern')->nullable(); // e.g., {'frequency': 'weekly', 'interval': 1, 'end_date': '...'}
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
