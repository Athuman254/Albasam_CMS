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
        Schema::table('exams', function (Blueprint $table) {
            $table->string('publisher')->nullable()->after('exam_type');
            $table->date('exam_date')->nullable()->after('publisher');
        });

        Schema::table('exam_subjects', function (Blueprint $table) {
            $table->dropColumn(['publisher', 'exam_date', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_subjects', function (Blueprint $table) {
            $table->string('publisher')->nullable();
            $table->date('exam_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['publisher', 'exam_date']);
        });
    }
};
