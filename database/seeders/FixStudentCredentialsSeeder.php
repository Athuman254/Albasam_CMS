<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class FixStudentCredentialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::whereNull('username')->orWhereNull('password')->get();

        $this->command->info("Fixing credentials for {$students->count()} students...");

        foreach ($students as $student) {
            $admissionNumber = $student->admission_number;

            if (!$admissionNumber) {
                $this->command->warn("Student ID {$student->id} has no admission number. Skipping.");
                continue;
            }

            // Password = Admission Number + Current Year (following controller logic)
            $defaultPassword = $admissionNumber . date('Y');

            $student->update([
                'username' => $admissionNumber,
                'password' => Hash::make($defaultPassword),
                'user_type' => 'student',
                'force_password_change' => true,
            ]);

            $this->command->info("  ✓ Updated {$admissionNumber}");
        }

        $this->command->info("Finished fixing student credentials!");
    }
}
