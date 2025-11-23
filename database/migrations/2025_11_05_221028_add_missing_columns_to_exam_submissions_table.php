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
        Schema::table('exam_submissions', function (Blueprint $table) {
            $table->integer('total_marks_count')->default(0)->after('subjects_count');
            $table->integer('marks_entered_count')->default(0)->after('total_marks_count');
            $table->decimal('completion_percentage', 5, 2)->default(0)->after('marks_entered_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_submissions', function (Blueprint $table) {
            $table->dropColumn(['total_marks_count', 'marks_entered_count', 'completion_percentage']);
        });
    }
};