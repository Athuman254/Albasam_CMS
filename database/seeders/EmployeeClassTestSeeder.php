<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Rank;
use App\Models\Subject;
use App\Models\AcademicYear;

class EmployeeClassTestSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create active academic year
        $academicYear = AcademicYear::firstOrCreate(
            ['is_active' => true],
            [
                'name' => '2024 Academic Year',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
            ]
        );

        // Get some active employees (teachers)
        $teachers = Employee::where('has_system_access', true)
            ->where('is_active', true)
            ->take(3)
            ->get();

        // Get some classes
        $classes = Rank::where('is_active', true)->take(2)->get();
        
        // Get some subjects
        $subjects = Subject::where('is_active', true)->take(3)->get();

        if ($teachers->isEmpty() || $classes->isEmpty() || $subjects->isEmpty()) {
            $this->command->info('Not enough data to seed employee-class relationships.');
            return;
        }

        $assignments = [];

        foreach ($teachers as $teacher) {
            foreach ($classes as $class) {
                // Assign 1-2 subjects per class for this teacher
                $assignedSubjects = $subjects->random(rand(1, 2));
                
                foreach ($assignedSubjects as $subject) {
                    $assignments[] = [
                        'employee_id' => $teacher->id,
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $academicYear->id,
                        'is_class_teacher' => rand(0, 1), // Randomly assign as class teacher
                        'notes' => 'Test assignment',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('employee_class')->insert($assignments);

        $this->command->info('Employee-Class test relationships seeded successfully.');
        $this->command->info('Total assignments created: ' . count($assignments));
    }
}