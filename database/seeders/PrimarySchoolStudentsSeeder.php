<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\StudentAdmission;
use App\Models\Rank;
use App\Models\Gender;
use App\Models\Religion;
use App\Models\Division;
use Carbon\Carbon;

class PrimarySchoolStudentsSeeder extends Seeder
{
    /**
     * Kenyan first names
     */
    private $kenyanFirstNames = [
        'male' => ['Juma', 'Kamau', 'Otieno', 'Mwangi', 'Kipchoge', 'Wanjiru', 'Omondi', 'Kimani', 'Njoroge', 'Kariuki', 'Mutua', 'Kiprotich', 'Barasa', 'Wekesa', 'Makori', 'Odhiambo', 'Koech', 'Rotich', 'Cheruiyot', 'Kiplagat'],
        'female' => ['Akinyi', 'Wanjiku', 'Njeri', 'Wambui', 'Chebet', 'Jebet', 'Nyambura', 'Wairimu', 'Adhiambo', 'Atieno', 'Mumbua', 'Chepkoech', 'Awino', 'Nyokabi', 'Wangari', 'Chemutai', 'Jepkorir', 'Wangui', 'Muthoni', 'Cherono']
    ];

    /**
     * Kenyan last names
     */
    private $kenyanLastNames = [
        'Ochieng',
        'Maina',
        'Onyango',
        'Njuguna',
        'Kiprop',
        'Wanjala',
        'Ouma',
        'Karanja',
        'Ndungu',
        'Kiptoo',
        'Mutiso',
        'Kibet',
        'Wafula',
        'Muriuki',
        'Korir',
        'Onyango',
        'Kimutai',
        'Kemboi',
        'Gitau',
        'Chepkwony',
        'Omondi',
        'Kamau',
        'Otieno',
        'Wanjiru',
        'Kipchoge',
        'Mwangi',
        'Kiprono',
        'Waithaka',
        'Mutua',
        'Jeptoo'
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating 50 Primary School Students...');

        // Get required data
        $division = Division::where('name', 'Primary School')->first();
        if (!$division) {
            $division = Division::first();
        }

        $genders = Gender::all();
        $maleGender = $genders->firstWhere('name', 'Male') ?? $genders->first();
        $femaleGender = $genders->firstWhere('name', 'Female') ?? $genders->last();

        $religions = Religion::all();

        // Get all primary school classes
        $classes = Rank::where('division_id', $division->id)
            ->orderBy('name')
            ->get();

        if ($classes->isEmpty()) {
            $this->command->error('No classes found! Please run PrimarySchoolClassesSeeder first.');
            return;
        }

        // Define age ranges for each class
        $ageRanges = [
            'PP1' => [4, 5],
            'PP2' => [5, 6],
            'Grade 1' => [6, 7],
            'Grade 2' => [7, 8],
            'Grade 3' => [8, 9],
            'Grade 4' => [9, 10],
            'Grade 5' => [10, 11],
            'Grade 6' => [11, 12],
        ];

        // Distribute 50 students across classes
        $studentsPerClass = (int) ceil(50 / $classes->count());
        $totalStudents = 0;
        $admissionCounter = 1;

        foreach ($classes as $class) {
            // Determine how many students for this class
            $remaining = 50 - $totalStudents;
            $count = min($studentsPerClass, $remaining);

            if ($count <= 0) break;

            // Get age range for this class
            $ageRange = $ageRanges[$class->name] ?? [6, 12];

            $this->command->info("Creating {$count} students for {$class->name}...");

            for ($i = 0; $i < $count; $i++) {
                // Randomly select gender
                $isMale = rand(0, 1) === 1;
                $gender = $isMale ? $maleGender : $femaleGender;

                // Select random name
                $firstName = $isMale
                    ? $this->kenyanFirstNames['male'][array_rand($this->kenyanFirstNames['male'])]
                    : $this->kenyanFirstNames['female'][array_rand($this->kenyanFirstNames['female'])];

                $lastName = $this->kenyanLastNames[array_rand($this->kenyanLastNames)];

                // Calculate date of birth based on age range
                $age = rand($ageRange[0], $ageRange[1]);
                $dateOfBirth = Carbon::now()->subYears($age)->subMonths(rand(0, 11));

                // Select random religion
                $religion = $religions->random();

                // Create student admission record
                $admission = StudentAdmission::create([
                    'date' => Carbon::now()->subMonths(rand(1, 12)),
                    'division_id' => $division->id,
                    'has_exit_school' => false,
                ]);

                // Create student with admission number format ADM01, ADM02, etc.
                $admissionNumber = 'ADM' . str_pad($admissionCounter, 2, '0', STR_PAD_LEFT);

                $student = Student::create([
                    'student_admission_id' => $admission->id,
                    'admission_number' => $admissionNumber,
                    'rank_id' => $class->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'gender_id' => $gender->id,
                    'religion_id' => $religion->id,
                    'date_of_birth' => $dateOfBirth->format('Y-m-d'),
                    'citizenship' => 'Kenyan',
                ]);

                $this->command->info("  ✓ {$admissionNumber}: {$firstName} {$lastName} - {$class->name}");

                $admissionCounter++;
                $totalStudents++;

                if ($totalStudents >= 50) break;
            }

            if ($totalStudents >= 50) break;
        }

        $this->command->info("\n✓ Successfully created {$totalStudents} students!");
        $this->command->info('Admission numbers: ADM01 to ADM' . str_pad($totalStudents, 2, '0', STR_PAD_LEFT));
    }
}
