<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\ExamStudent;
use App\Models\ExamMark;
use App\Models\Student;
use App\Models\Rank;
use App\Models\Subject;
use App\Models\Settings\AcademicYear;
use App\Models\Employee;

class ExamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get current academic year
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            $this->command->warn('⚠️  No active academic year found. Skipping exams seeder.');
            return;
        }

        // Get all classes and subjects
        $classes = Rank::all();
        $subjects = Subject::all();
        $students = Student::with('rank')->get();
        $teachers = Employee::whereHas('employeeClasses')->get();

        if ($classes->isEmpty() || $subjects->isEmpty() || $students->isEmpty()) {
            $this->command->warn('⚠️  Missing required data (classes, subjects, or students). Skipping exams seeder.');
            return;
        }

        // Define exam terms
        $examTypes = [
            ['name' => 'Term 1 Mid-Term Exam', 'term' => 'Term 1', 'status' => 'completed', 'start_offset' => -120, 'end_offset' => -115],
            ['name' => 'Term 1 End-Term Exam', 'term' => 'Term 1', 'status' => 'completed', 'start_offset' => -90, 'end_offset' => -85],
            ['name' => 'Term 2 Mid-Term Exam', 'term' => 'Term 2', 'status' => 'completed', 'start_offset' => -60, 'end_offset' => -55],
            ['name' => 'Term 2 End-Term Exam', 'term' => 'Term 2', 'status' => 'active', 'start_offset' => -30, 'end_offset' => -25],
            ['name' => 'Term 3 Mid-Term Exam', 'term' => 'Term 3', 'status' => 'draft', 'start_offset' => 10, 'end_offset' => 15],
            ['name' => 'Term 3 End-Term Exam', 'term' => 'Term 3', 'status' => 'draft', 'start_offset' => 40, 'end_offset' => 45],
        ];

        foreach ($examTypes as $examData) {
            // Create Exam
            $exam = Exam::firstOrCreate(
                [
                    'name' => $examData['name'],
                    'academic_year_id' => $currentAcademicYear->id,
                ],
                [
                    'term' => $examData['term'],
                    'status' => $examData['status'],
                    'start_date' => now()->addDays($examData['start_offset']),
                    'end_date' => now()->addDays($examData['end_offset']),
                ]
            );

            $this->command->info("Created exam: {$exam->name}");

            // Create exam subjects for each class
            foreach ($classes as $class) {
                // Get subjects relevant to this class level
                $classSubjects = $this->getSubjectsForClass($subjects, $class);

                foreach ($classSubjects as $index => $subject) {
                    $examSubject = ExamSubject::firstOrCreate(
                        [
                            'exam_id' => $exam->id,
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                        ],
                        [
                            'exam_date' => now()->addDays($examData['start_offset'] + $index),
                            'start_time' => '08:00:00',
                            'end_time' => '10:00:00',
                            'max_marks' => 100,
                        ]
                    );

                    // Enroll students in this exam
                    $classStudents = $students->where('rank_id', $class->id);

                    foreach ($classStudents as $student) {
                        ExamStudent::firstOrCreate([
                            'exam_id' => $exam->id,
                            'class_id' => $class->id,
                            'student_id' => $student->id,
                        ]);
                    }

                    // Generate marks for completed exams (30-40% of students)
                    if ($examData['status'] == 'completed') {
                        $studentsToMark = $classStudents->random(min(ceil($classStudents->count() * 0.35), $classStudents->count()));

                        foreach ($studentsToMark as $student) {
                            // Get a teacher for this subject
                            $teacher = $teachers->random();

                            // Generate realistic marks (40-95 range with normal distribution)
                            $marksObtained = $this->generateRealisticMarks();
                            $grade = $this->calculateGrade($marksObtained);

                            ExamMark::firstOrCreate(
                                [
                                    'exam_id' => $exam->id,
                                    'exam_subject_id' => $examSubject->id,
                                    'student_id' => $student->id,
                                    'class_id' => $class->id,
                                ],
                                [
                                    'teacher_id' => $teacher->id,
                                    'marks_obtained' => $marksObtained,
                                    'maximum_marks' => 100,
                                    'grade' => $grade,
                                    'status' => 'approved',
                                    'submitted_by' => $teacher->user_id,
                                    'submitted_at' => now()->subDays(rand(1, 10)),
                                    'approved_by' => $teacher->user_id,
                                    'approved_at' => now()->subDays(rand(1, 5)),
                                    'remarks' => $this->generateRemarks($marksObtained),
                                ]
                            );
                        }
                    }
                }
            }
        }

        $this->command->info('✅ Successfully created exams with subjects, enrolled students, and sample marks!');
    }

    /**
     * Get subjects appropriate for a class level
     */
    private function getSubjectsForClass($subjects, $class)
    {
        // For primary school, common subjects are:
        // Math, English, Kiswahili, Science, Social Studies, CRE
        $primarySubjects = ['Mathematics', 'English', 'Kiswahili', 'Science', 'Social Studies', 'CRE'];

        return $subjects->filter(function ($subject) use ($primarySubjects) {
            foreach ($primarySubjects as $primarySubject) {
                if (stripos($subject->name, $primarySubject) !== false) {
                    return true;
                }
            }
            return false;
        })->take(6); // Limit to 6 subjects per class
    }

    /**
     * Generate realistic marks with normal distribution
     */
    private function generateRealisticMarks()
    {
        // Generate marks with normal distribution centered around 65
        // Using Box-Muller transform for normal distribution
        $mean = 65;
        $stdDev = 15;

        $u1 = mt_rand() / mt_getrandmax();
        $u2 = mt_rand() / mt_getrandmax();

        $z = sqrt(-2 * log($u1)) * cos(2 * pi() * $u2);
        $marks = round($mean + $z * $stdDev);

        // Clamp between 40 and 95
        return max(40, min(95, $marks));
    }

    /**
     * Calculate grade based on marks
     */
    private function calculateGrade($marks)
    {
        if ($marks >= 80) return 'A';
        if ($marks >= 75) return 'A-';
        if ($marks >= 70) return 'B+';
        if ($marks >= 65) return 'B';
        if ($marks >= 60) return 'B-';
        if ($marks >= 55) return 'C+';
        if ($marks >= 50) return 'C';
        if ($marks >= 45) return 'C-';
        if ($marks >= 40) return 'D+';
        if ($marks >= 35) return 'D';
        if ($marks >= 30) return 'D-';
        return 'E';
    }

    /**
     * Generate remarks based on marks
     */
    private function generateRemarks($marks)
    {
        if ($marks >= 80) {
            return 'Excellent performance! Keep up the great work.';
        } elseif ($marks >= 70) {
            return 'Very good work. Continue with the same effort.';
        } elseif ($marks >= 60) {
            return 'Good performance. There is room for improvement.';
        } elseif ($marks >= 50) {
            return 'Fair performance. More effort needed.';
        } else {
            return 'Needs improvement. Extra support recommended.';
        }
    }
}
