<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exam_submissions', function (Blueprint $table) {
            $table->integer('students_count')->default(0)->after('submitted_at');
            $table->integer('subjects_count')->default(0)->after('students_count');
        });

        // Update existing records with calculated counts
        DB::statement("
            UPDATE exam_submissions es
            JOIN (
                SELECT 
                    exam_submission_id,
                    COUNT(DISTINCT student_id) as students_count,
                    COUNT(DISTINCT exam_subject_id) as subjects_count
                FROM exam_marks
                GROUP BY exam_submission_id
            ) em ON es.id = em.exam_submission_id
            SET es.students_count = em.students_count,
                es.subjects_count = em.subjects_count
        ");
    }

    public function down()
    {
        Schema::table('exam_submissions', function (Blueprint $table) {
            $table->dropColumn(['students_count', 'subjects_count']);
        });
    }
};