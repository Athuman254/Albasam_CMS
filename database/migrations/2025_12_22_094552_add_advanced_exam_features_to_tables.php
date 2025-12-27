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
            $table->enum('exam_type', ['opening', 'mid', 'end', 'general'])->default('general')->after('term');
        });

        Schema::table('exam_subjects', function (Blueprint $table) {
            $table->string('publisher')->nullable()->after('max_marks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('exam_type');
        });

        Schema::table('exam_subjects', function (Blueprint $table) {
            $table->dropColumn('publisher');
        });
    }
};
