<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherQualificationsSeeder extends Seeder
{
    /**
     * Seed teacher qualifications (teacher-subject assignments).
     */
    public function run(): void
    {
        $this->command->info('Seeding teacher qualifications...');

        // Clear existing qualifications
        DB::table('teacher_qualifications')->truncate();

        $teachers = Teacher::all();
        $subjects = Subject::where('activated', 1)->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No teachers found. Please run TeachersSeeder first.');
            return;
        }

        if ($subjects->isEmpty()) {
            $this->command->warn('No subjects found. Please run SubjectsSeeder first.');
            return;
        }

        // Define subject categories for better assignment
        $scienceSubjects = $subjects->filter(function ($subject) {
            return in_array(strtolower($subject->name), [
                'mathematics',
                'physics',
                'chemistry',
                'biology',
                'science'
            ]);
        });

        $languageSubjects = $subjects->filter(function ($subject) {
            return in_array(strtolower($subject->name), [
                'english',
                'kiswahili',
                'french',
                'german',
                'arabic'
            ]);
        });

        $humanitiesSubjects = $subjects->filter(function ($subject) {
            return in_array(strtolower($subject->name), [
                'history',
                'geography',
                'cre',
                'ire',
                'hre',
                'social studies'
            ]);
        });

        $otherSubjects = $subjects->diff($scienceSubjects)
            ->diff($languageSubjects)
            ->diff($humanitiesSubjects);

        $qualificationsCount = 0;

        foreach ($teachers as $index => $teacher) {
            // Assign 2-4 subjects per teacher based on their specialization
            $numSubjects = rand(2, 4);
            $assignedSubjects = collect();

            // Determine teacher's area based on index (for variety)
            $teacherType = $index % 4; // 0=Science, 1=Languages, 2=Humanities, 3=Mixed

            switch ($teacherType) {
                case 0: // Science teacher
                    if ($scienceSubjects->isNotEmpty()) {
                        $assignedSubjects = $scienceSubjects->random(min($numSubjects, $scienceSubjects->count()));
                    }
                    break;

                case 1: // Language teacher
                    if ($languageSubjects->isNotEmpty()) {
                        $assignedSubjects = $languageSubjects->random(min($numSubjects, $languageSubjects->count()));
                    }
                    break;

                case 2: // Humanities teacher
                    if ($humanitiesSubjects->isNotEmpty()) {
                        $assignedSubjects = $humanitiesSubjects->random(min($numSubjects, $humanitiesSubjects->count()));
                    }
                    break;

                case 3: // Mixed/General teacher
                    // Pick from any category
                    $allAvailable = $subjects->shuffle();
                    $assignedSubjects = $allAvailable->take($numSubjects);
                    break;
            }

            // If no subjects assigned yet (edge case), assign random subjects
            if ($assignedSubjects->isEmpty() && $subjects->isNotEmpty()) {
                $assignedSubjects = $subjects->random(min($numSubjects, $subjects->count()));
            }

            // Create the qualifications
            foreach ($assignedSubjects as $subject) {
                DB::table('teacher_qualifications')->insert([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $qualificationsCount++;
            }
        }

        $this->command->info("Teacher qualifications seeded: {$qualificationsCount} assignments created.");
        $this->command->info("Average subjects per teacher: " . round($qualificationsCount / max($teachers->count(), 1), 1));
    }
}
