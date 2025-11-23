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
        Schema::table('exam_marks', function (Blueprint $table) {
            // Only add columns if they don't exist
            if (!Schema::hasColumn('exam_marks', 'submitted_by')) {
                $table->unsignedBigInteger('submitted_by')->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('exam_marks', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            }
            if (!Schema::hasColumn('exam_marks', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('exam_marks', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_marks', function (Blueprint $table) {
            $table->dropColumn(['submitted_by', 'submitted_at', 'approved_by', 'approved_at']);
        });
    }
};