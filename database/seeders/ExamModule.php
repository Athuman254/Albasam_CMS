<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ExamModule extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('academic_years')->insert([
          [
                'name' => '2027-2028',
                'start_date' => '2027-01-01',
                'end_date' => '2027-12-31',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
          [
                'name' => '2026-2027',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '2025-2026',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
        return;
       DB::table('exams')->insert([
            [
                'name' => 'Midterm Exam',
                'term' => 'Term 1',
                'academic_year_id' => 1,
                'start_date' => '2025-02-15',
                'end_date' => '2025-02-25',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'End Term Exam',
                'term' => 'Term 1',
                'academic_year_id' => 1,
                'start_date' => '2025-06-01',
                'end_date' => '2025-06-15',
                'status' => 'draft',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        DB::table('exam_subjects')->insert([
            [
                'exam_id' => 1,
                'class_id' => 1,
                'subject_id' => 1,
                'exam_date' => '2025-02-16',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'max_marks' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'exam_id' => 1,
                'class_id' => 1,
                'subject_id' => 2,
                'exam_date' => '2025-02-17',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'max_marks' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('grading_scheme')->insert([
            ['name' => 'Default Scheme', 'min_score' => 80, 'max_score' => 100, 'grade' => 'A', 'remark' => 'Excellent', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Default Scheme', 'min_score' => 70, 'max_score' => 79, 'grade' => 'B', 'remark' => 'Very Good', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Default Scheme', 'min_score' => 60, 'max_score' => 69, 'grade' => 'C', 'remark' => 'Good', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Default Scheme', 'min_score' => 50, 'max_score' => 59, 'grade' => 'D', 'remark' => 'Fair', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Default Scheme', 'min_score' => 0,  'max_score' => 49, 'grade' => 'E', 'remark' => 'Needs Improvement', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
