<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiagnoseEnrollmentSeeder extends Seeder
{
    /**
     * Run diagnostic checks for enrollment and enter marks functionality
     */
    public function run()
    {
        $this->command->info('=== ENROLLMENT & ENTER MARKS DIAGNOSTIC ===');
        $this->command->newLine();

        // 1. Check exam_students table structure
        $this->command->info('1. Checking exam_students table structure...');
        try {
            $columns = DB::select("DESCRIBE exam_students");
            $this->command->table(
                ['Field', 'Type', 'Null', 'Key'],
                collect($columns)->map(fn($col) => [
                    $col->Field,
                    $col->Type,
                    $col->Null,
                    $col->Key
                ])
            );
        } catch (\Exception $e) {
            $this->command->error('Error: ' . $e->getMessage());
        }
        $this->command->newLine();

        // 2. Check enrolled students count
        $this->command->info('2. Checking enrolled students...');
        $enrolledCount = DB::table('exam_students')->count();
        $this->command->info("Total enrolled records: {$enrolledCount}");

        if ($enrolledCount > 0) {
            $sample = DB::table('exam_students')
                ->join('students', 'exam_students.student_id', '=', 'students.id')
                ->join('exams', 'exam_students.exam_id', '=', 'exams.id')
                ->join('ranks', 'exam_students.class_id', '=', 'ranks.id')
                ->select(
                    'exams.name as exam_name',
                    'ranks.name as class_name',
                    'students.first_name',
                    'students.last_name',
                    'students.admission_number'
                )
                ->limit(5)
                ->get();

            $this->command->table(
                ['Exam', 'Class', 'Student', 'Admission No'],
                $sample->map(fn($s) => [
                    $s->exam_name,
                    $s->class_name,
                    $s->first_name . ' ' . $s->last_name,
                    $s->admission_number
                ])
            );
        }
        $this->command->newLine();

        // 3. Check specific enrollment (Term 1, Grade 1)
        $this->command->info('3. Checking Term 1 End-Term Exam, Grade 1 enrollment...');

        $exam = DB::table('exams')
            ->where('name', 'LIKE', '%Term 1%End-Term%')
            ->first();

        $class = DB::table('ranks')
            ->where('name', 'LIKE', '%Grade 1%')
            ->first();

        if ($exam && $class) {
            $this->command->info("Exam ID: {$exam->id} - {$exam->name}");
            $this->command->info("Class ID: {$class->id} - {$class->name}");

            $enrolledStudents = DB::table('exam_students')
                ->where('exam_id', $exam->id)
                ->where('class_id', $class->id)
                ->count();

            $this->command->info("Enrolled students: {$enrolledStudents}");

            if ($enrolledStudents > 0) {
                $students = DB::table('exam_students')
                    ->join('students', 'exam_students.student_id', '=', 'students.id')
                    ->where('exam_students.exam_id', $exam->id)
                    ->where('exam_students.class_id', $class->id)
                    ->select(
                        'students.id',
                        'students.admission_number',
                        'students.first_name',
                        'students.last_name'
                    )
                    ->get();

                $this->command->table(
                    ['ID', 'Admission No', 'Name'],
                    $students->map(fn($s) => [
                        $s->id,
                        $s->admission_number,
                        $s->first_name . ' ' . $s->last_name
                    ])
                );
            }
        } else {
            if (!$exam) $this->command->error('Exam not found!');
            if (!$class) $this->command->error('Class not found!');
        }
        $this->command->newLine();

        // 4. Test the query used in getEnrolledStudentsForMarks
        $this->command->info('4. Testing getEnrolledStudentsForMarks query...');
        if ($exam && $class) {
            try {
                $students = DB::table('exam_students')
                    ->join('students', 'exam_students.student_id', '=', 'students.id')
                    ->leftJoin('users', 'students.user_id', '=', 'users.id')
                    ->leftJoin('ranks', 'students.rank_id', '=', 'ranks.id')
                    ->where('exam_students.exam_id', $exam->id)
                    ->where('exam_students.class_id', $class->id)
                    ->select(
                        'students.id',
                        'students.admission_number as student_id',
                        'students.first_name',
                        'students.last_name',
                        DB::raw("CONCAT(students.first_name, ' ', students.last_name) as name"),
                        'users.email',
                        'students.admission_number',
                        'ranks.name as class_name'
                    )
                    ->get();

                $this->command->info("Query returned: {$students->count()} students");

                if ($students->count() > 0) {
                    $this->command->table(
                        ['ID', 'Admission', 'Name', 'Email', 'Class'],
                        $students->map(fn($s) => [
                            $s->id,
                            $s->admission_number,
                            $s->name,
                            $s->email ?? 'N/A',
                            $s->class_name ?? 'N/A'
                        ])
                    );
                    $this->command->info('✅ Query works correctly!');
                } else {
                    $this->command->error('❌ Query returned 0 students!');
                }
            } catch (\Exception $e) {
                $this->command->error('Query failed: ' . $e->getMessage());
            }
        }
        $this->command->newLine();

        // 5. Summary
        $this->command->info('=== DIAGNOSTIC SUMMARY ===');
        $this->command->info('✅ Enrollment table exists and has structure');
        $this->command->info("✅ Total enrollments: {$enrolledCount}");

        if ($exam && $class) {
            $count = DB::table('exam_students')
                ->where('exam_id', $exam->id)
                ->where('class_id', $class->id)
                ->count();

            if ($count > 0) {
                $this->command->info("✅ Term 1 Grade 1 has {$count} enrolled students");
                $this->command->info('✅ Query structure is correct');
                $this->command->newLine();
                $this->command->info('🎯 CONCLUSION: Enrollment is working correctly!');
                $this->command->info('   Upload the updated EmployeeController.php to fix Enter Marks.');
            } else {
                $this->command->error('❌ No students enrolled in Term 1 Grade 1');
                $this->command->info('   Please enroll students first.');
            }
        }
    }
}
