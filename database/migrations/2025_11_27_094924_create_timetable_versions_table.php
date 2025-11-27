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
        Schema::create('timetable_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('version_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false); // Only one active version per academic year
            $table->boolean('is_published')->default(false); // Visible to students/parents
            $table->json('generation_stats')->nullable(); // Store conflicts, attempts, coverage stats
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['academic_year_id', 'is_active']);
            $table->index(['is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_versions');
    }
};
