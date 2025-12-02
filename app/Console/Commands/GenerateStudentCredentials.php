<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GenerateStudentCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'student:generate-credentials {--force : Overwrite existing credentials}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate login credentials for students who do not have them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting credential generation for students...');

        $query = Student::query();

        if (!$this->option('force')) {
            $query->whereNull('username')->orWhereNull('password');
        }

        $students = $query->get();
        $count = $students->count();

        if ($count === 0) {
            $this->info('No students found needing credentials.');
            return;
        }

        $this->withProgressBar($students, function ($student) {
            $admissionNumber = $student->admission_number;

            // Skip if no admission number
            if (empty($admissionNumber)) {
                return;
            }

            $username = $admissionNumber;
            // Password: Admission Number + Current Year (or admission year if available, but let's stick to current year for simplicity or 2024)
            // Better: Admission Number + Year of Admission (if we can get it from created_at)
            $year = $student->created_at ? $student->created_at->format('Y') : date('Y');
            $plainPassword = $admissionNumber . $year;

            $student->update([
                'username' => $username,
                'password' => Hash::make($plainPassword),
                'user_type' => 'student',
                'force_password_change' => true,
            ]);
        });

        $this->newLine();
        $this->info("Successfully generated credentials for {$count} students.");
        $this->info("Default Password Format: AdmissionNumber + AdmissionYear (e.g., ADM0012024)");
    }
}
