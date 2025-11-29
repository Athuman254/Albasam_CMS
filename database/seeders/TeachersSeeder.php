<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;
use App\Models\Teacher;
use App\Models\EmployeeClass;
use App\Models\Gender;
use App\Models\Religion;
use App\Models\EmploymentType;
use App\Models\EmploymentStatus;
use App\Models\Honorific;
use App\Models\JobTitle;
use App\Models\Specialization;
use App\Models\Rank;
use App\Models\Subject;
use App\Models\Settings\AcademicYear;

use App\Models\User;

class TeachersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing lookup data
        $maleGender = Gender::where('name', 'Male')->first();
        $femaleGender = Gender::where('name', 'Female')->first();
        $christianReligion = Religion::where('name', 'Christian')->first();
        $muslimReligion = Religion::where('name', 'Muslim')->first();

        $employmentType = EmploymentType::first(); // Use first available
        $employmentStatus = EmploymentStatus::first(); // Use first available

        $mrHonorific = Honorific::where('name', 'Mr.')->first();
        $msHonorific = Honorific::where('name', 'Ms.')->first();
        $mrsHonorific = Honorific::where('name', 'Mrs.')->first();

        // Get existing job titles
        $jobTitles = JobTitle::take(3)->get();

        // Get existing specializations
        if (Specialization::count() === 0) {
            $this->command->warn('No specializations found. Creating basic ones...');
            Specialization::create(['area' => 'Mathematics']);
            Specialization::create(['area' => 'Science']);
            Specialization::create(['area' => 'Languages']);
            Specialization::create(['area' => 'Social Studies']);
        }
        $specializations = Specialization::take(4)->get();

        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        // Get classes and subjects
        $classes = Rank::all();
        $subjects = Subject::all();

        // Kenyan teacher names (mix of male and female)
        $teachers = [
            ['first_name' => 'James', 'middle_name' => 'Kamau', 'last_name' => 'Mwangi', 'gender' => $maleGender, 'honorific' => $mrHonorific, 'religion' => $christianReligion, 'experience' => 15],
            ['first_name' => 'Mary', 'middle_name' => 'Wanjiru', 'last_name' => 'Njeri', 'gender' => $femaleGender, 'honorific' => $mrsHonorific, 'religion' => $christianReligion, 'experience' => 12],
            ['first_name' => 'Peter', 'middle_name' => 'Ochieng', 'last_name' => 'Otieno', 'gender' => $maleGender, 'honorific' => $mrHonorific, 'religion' => $christianReligion, 'experience' => 10],
            ['first_name' => 'Grace', 'middle_name' => 'Akinyi', 'last_name' => 'Adhiambo', 'gender' => $femaleGender, 'honorific' => $msHonorific, 'religion' => $christianReligion, 'experience' => 8],
            ['first_name' => 'John', 'middle_name' => 'Kipchoge', 'last_name' => 'Rotich', 'gender' => $maleGender, 'honorific' => $mrHonorific, 'religion' => $christianReligion, 'experience' => 7],
            ['first_name' => 'Faith', 'middle_name' => 'Chebet', 'last_name' => 'Koech', 'gender' => $femaleGender, 'honorific' => $msHonorific, 'religion' => $christianReligion, 'experience' => 9],
            ['first_name' => 'David', 'middle_name' => 'Maina', 'last_name' => 'Kariuki', 'gender' => $maleGender, 'honorific' => $mrHonorific, 'religion' => $christianReligion, 'experience' => 6],
            ['first_name' => 'Sarah', 'middle_name' => 'Wambui', 'last_name' => 'Kamau', 'gender' => $femaleGender, 'honorific' => $mrsHonorific, 'religion' => $christianReligion, 'experience' => 11],
            ['first_name' => 'Daniel', 'middle_name' => 'Onyango', 'last_name' => 'Omondi', 'gender' => $maleGender, 'honorific' => $mrHonorific, 'religion' => $christianReligion, 'experience' => 5],
            ['first_name' => 'Lucy', 'middle_name' => 'Njoki', 'last_name' => 'Wanjiku', 'gender' => $femaleGender, 'honorific' => $msHonorific, 'religion' => $christianReligion, 'experience' => 8],
            ['first_name' => 'Mohamed', 'middle_name' => 'Hassan', 'last_name' => 'Ali', 'gender' => $maleGender, 'honorific' => $mrHonorific, 'religion' => $muslimReligion ?? $christianReligion, 'experience' => 10],
            ['first_name' => 'Fatuma', 'middle_name' => 'Amina', 'last_name' => 'Mohamed', 'gender' => $femaleGender, 'honorific' => $msHonorific, 'religion' => $muslimReligion ?? $christianReligion, 'experience' => 7],
            ['first_name' => 'Samuel', 'middle_name' => 'Kimani', 'last_name' => 'Ndung\'u', 'gender' => $maleGender, 'honorific' => $mrHonorific, 'religion' => $christianReligion, 'experience' => 6],
            ['first_name' => 'Elizabeth', 'middle_name' => 'Nyambura', 'last_name' => 'Githinji', 'gender' => $femaleGender, 'honorific' => $mrsHonorific, 'religion' => $christianReligion, 'experience' => 9],
            ['first_name' => 'Patrick', 'middle_name' => 'Mutua', 'last_name' => 'Musyoka', 'gender' => $maleGender, 'honorific' => $mrHonorific, 'religion' => $christianReligion, 'experience' => 8],
            ['first_name' => 'Alice', 'middle_name' => 'Muthoni', 'last_name' => 'Kibera', 'gender' => $femaleGender, 'honorific' => $mrsHonorific, 'religion' => $christianReligion, 'experience' => 5],
            ['first_name' => 'George', 'middle_name' => 'Odhiambo', 'last_name' => 'Ouma', 'gender' => $maleGender, 'honorific' => $mrHonorific, 'religion' => $christianReligion, 'experience' => 12],
            ['first_name' => 'Hellen', 'middle_name' => 'Wangari', 'last_name' => 'Njoroge', 'gender' => $femaleGender, 'honorific' => $msHonorific, 'religion' => $christianReligion, 'experience' => 4],
            ['first_name' => 'Kevin', 'middle_name' => 'Kiptoo', 'last_name' => 'Cheruiyot', 'gender' => $maleGender, 'honorific' => $mrHonorific, 'religion' => $christianReligion, 'experience' => 6],
            ['first_name' => 'Brenda', 'middle_name' => 'Achieng', 'last_name' => 'Okoth', 'gender' => $femaleGender, 'honorific' => $msHonorific, 'religion' => $christianReligion, 'experience' => 7],
        ];

        $tscCounter = 100001;
        $createdCount = 0;

        foreach ($teachers as $index => $teacherData) {
            // Create User
            $user = User::firstOrCreate(
                ['email' => strtolower($teacherData['first_name'] . '.' . $teacherData['last_name']) . '@school.ac.ke'],
                [
                    'name' => $teacherData['first_name'] . ' ' . $teacherData['last_name'],
                    'username' => strtolower($teacherData['first_name'] . '.' . $teacherData['last_name']),
                    'password' => Hash::make('password'),
                ]
            );

            // Create or Update Employee
            $employee = Employee::updateOrCreate(
                ['email' => $user->email],
                [
                    'user_id' => $user->id,
                    'staff_number' => 'EMP-' . str_pad($index + 1, 7, '0', STR_PAD_LEFT),
                    'first_name' => $teacherData['first_name'],
                    'middle_name' => $teacherData['middle_name'],
                    'last_name' => $teacherData['last_name'],
                    'gender_id' => $teacherData['gender']->id,
                    'religion_id' => $teacherData['religion']->id,
                    'honorific_id' => $teacherData['honorific']?->id,
                    'employment_type_id' => $employmentType->id,
                    'employment_status_id' => $employmentStatus->id,
                    'primary_phone' => '07' . rand(10000000, 99999999),
                    'secondary_phone' => '07' . rand(10000000, 99999999),
                    'identification_number' => rand(10000000, 39999999),
                    'tax_identification_pin' => 'A' . rand(100000000, 999999999) . chr(rand(65, 90)),
                    'permanent_physical_address' => 'P.O. Box ' . rand(1000, 9999) . ', Nairobi',
                    'date_of_hire' => now()->subYears($teacherData['experience'])->format('Y-m-d'),
                    'has_system_access' => true,
                    'in_payroll' => true,
                ]
            );

            // Create or Update Teacher record
            $jobTitle = $jobTitles->get($index % $jobTitles->count());
            $specialization = $specializations->get($index % $specializations->count());

            $teacher = Teacher::updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'user_id' => $user->id,
                    'first_name' => $teacherData['first_name'],
                    'middle_name' => $teacherData['middle_name'],
                    'last_name' => $teacherData['last_name'],
                    'honorific_id' => $teacherData['honorific']?->id,
                    'job_title_id' => $jobTitle->id,
                    'specialization_area_id' => $specialization->id,
                    'tsc_number' => 'TSC-' . $tscCounter++,
                    'years_of_experience' => $teacherData['experience'],
                ]
            );

            // Assign classes and subjects (if academic year exists)
            if ($currentAcademicYear && $classes->count() > 0 && $subjects->count() > 0) {
                // Assign 2-3 classes to each teacher
                $numClasses = min(3, $classes->count());
                $assignedClasses = $classes->random($numClasses);

                foreach ($assignedClasses as $classIndex => $class) {
                    // Get a random subject
                    $subject = $subjects->random();

                    // First class assignment makes them a class teacher
                    $isClassTeacher = ($classIndex == 0 && $index < 8); 

                    EmployeeClass::firstOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'academic_year_id' => $currentAcademicYear->id,
                        ],
                        [
                            'is_class_teacher' => $isClassTeacher,
                            'notes' => $isClassTeacher ? 'Class Teacher for ' . $class->name : null,
                        ]
                    );
                }
            }

            $createdCount++;
        }

        $this->command->info("✅ Successfully created {$createdCount} teachers with class assignments!");
    }
}
