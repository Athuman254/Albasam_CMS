<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Division;
use App\Models\EmploymentStatus;
use App\Models\EmploymentType;
use App\Models\Gender;
use App\Models\Honorific;
use App\Models\Institution;
use App\Models\JobTitle;
use App\Models\MaritalStatus;
use App\Models\QualificationType;
use App\Models\Rank;
use App\Models\Relationship;
use App\Models\Religion;
use App\Models\SalaryGrade;
use App\Models\SalaryScale;
use App\Models\Specialization;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\TeacherTitle;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Model::unguard();
//        $this->call(AttendanceSeeder::class);
//        $this->call(ServiceSeeder::class);

        Schema::disableForeignKeyConstraints();

        $this->departments();

        $this->genders();

        $this->religions();

        $this->relationships();

        $this->streams();

        $this->divisions();
        
        $this->ranks();
        
        $this->subjects();

        $this->maritalStatuses();

        $this->honorifics();

        $this->employmentStatuses();

        $this->employmentTypes();

        $this->jobTitles();

        $this->specialisations();

        $this->qualificationTypes();

        $this->salaryGrades();

        $this->salaryScales();

        $this->institution();

        User::create([
            'name'      => 'John Doe',
            'username'  => 'admin',
            'email'     => 'admin@app.com',
            'phone'     => '0712345678',
            'password'  => Hash::make('admin@!2025'),
            // 'is_admin' => true,
        ]);

        $this->call(LaratrustSeeder::class);
    }

    public function departments(): void
    {
        Department::truncate();

        Department::insert([
            ['name' => 'Science'],
            ['name' => 'Languages'],
            ['name' => 'Library'],
        ]);
    }

    public function genders(): void
    {
        Gender::truncate();

        Gender::insert([
            ['name' => 'Male'],
            ['name' => 'Female'],
            ['name' => 'Other'],
        ]);
    }

    public function religions(): void
    {
        Religion::truncate();

        Religion::insert([
            ['name' => 'Christian'],
            ['name' => 'Islam'],
            ['name' => 'Hindu'],
            ['name' => 'Other'],
        ]);
    }

    public function relationships(): void
    {
        Relationship::truncate();

        Relationship::insert([
            ['name' => 'Father'],
            ['name' => 'Mother'],
            ['name' => 'Husband'],
            ['name' => 'Wife'],
            ['name' => 'Brother'],
            ['name' => 'Sister'],
            ['name' => 'Uncle'],
            ['name' => 'Aunt'],
            ['name' => 'Grandparent'],
            ['name' => 'Other'],
        ]);
    }

    public function streams(): void
    {
        Stream::truncate();

        Stream::insert([
            ['name' => 'North'],
            ['name' => 'South'],
            ['name' => 'East'],
            ['name' => 'West'],
//            ['name' => 'Red'],
//            ['name' => 'Green'],
//            ['name' => 'Blue'],
//            ['name' => 'Yellow'],
        ]);
    }

    public function divisions(): void
    {
        Division::truncate();

        Division::insert([
//            ['name' => 'Pre-Primary'],
//            ['name' => 'Primary'],
//            ['name' => 'Junior Secondary'],
//            ['name' => 'Senior Secondary'],
            ['name' => 'Secondary School'],
            ['name' => 'High School'],
        ]);
    }
    
    public function ranks(): void
    {
        Rank::truncate();
        
        $divisions = Division::all();
        $streams = Stream::all();
        
        Rank::insert([
            [
                'name' => 'Form 1',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'North')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 1',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'South')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 1',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'East')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 1',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'West')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 2',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'North')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 2',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'South')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 2',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'East')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 2',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'West')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 3',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'North')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 3',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'South')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 3',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'East')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 3',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'West')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 4',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'North')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 4',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'South')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 4',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'East')->first()->id,
                'activated' => true,
            ],
            [
                'name' => 'Form 4',
                'division_id' => $divisions->where('name', '=', 'Secondary School')->first()->id,
                'stream_id' => $streams->where('name', '=', 'West')->first()->id,
                'activated' => true,
            ],
        ]);
    }
    
    public function subjects(): void
    {
        Subject::truncate();
        
        Subject::insert([
            ['name' => 'English', 'group' => Subject::LANGUAGES, 'activated' => true],
            ['name' => 'Kiswahili', 'group' => Subject::LANGUAGES, 'activated' => true],
            ['name' => 'Arabic', 'group' => Subject::LANGUAGES, 'activated' => true],
            ['name' => 'French', 'group' => Subject::LANGUAGES, 'activated' => true],
            ['name' => 'German', 'group' => Subject::LANGUAGES, 'activated' => true],
            ['name' => 'Mathematics', 'group' => Subject::SCIENCE, 'activated' => true],
            ['name' => 'Chemistry', 'group' => Subject::SCIENCE, 'activated' => true],
            ['name' => 'Physics', 'group' => Subject::SCIENCE, 'activated' => true],
            ['name' => 'Biology', 'group' => Subject::SCIENCE, 'activated' => true],
            ['name' => 'Home Science', 'group' => Subject::APPLIED_SCIENCE, 'activated' => true],
            ['name' => 'Agriculture', 'group' => Subject::APPLIED_SCIENCE, 'activated' => true],
            ['name' => 'Computer Studies', 'group' => Subject::APPLIED_SCIENCE, 'activated' => true],
            ['name' => 'History', 'group' => Subject::HUMANITIES, 'activated' => true],
            ['name' => 'Geography', 'group' => Subject::HUMANITIES, 'activated' => true],
            ['name' => 'Religious Education', 'group' => Subject::HUMANITIES, 'activated' => true],
            ['name' => 'Business Studies', 'group' => Subject::HUMANITIES, 'activated' => true],
            ['name' => 'Life Skills', 'group' => Subject::HUMANITIES, 'activated' => true],
            ['name' => 'Music', 'group' => Subject::CREATIVE_ARTS, 'activated' => true],
            ['name' => 'Art and Design', 'group' => Subject::CREATIVE_ARTS, 'activated' => true],
        ]);
    }

    public function maritalStatuses(): void
    {
        MaritalStatus::truncate();

        MaritalStatus::insert([
            ['name' => 'Married'],
            ['name' => 'Single'],
            ['name' => 'Single-Parent'],
            ['name' => 'Other'],
        ]);
    }

    public function honorifics(): void
    {
        Honorific::truncate();

        Honorific::insert([
            ['name' => 'Mr.'],
            ['name' => 'Mrs.'],
            ['name' => 'Md.'],
            ['name' => 'Prof.'],
            ['name' => 'Lec.'],
        ]);
    }

    public function employmentTypes(): void
    {
        EmploymentType::truncate();

        EmploymentType::insert([
            ['name' => 'Pensionable'],
            ['name' => 'Full-Time'],
            ['name' => 'Part-Time'],
            ['name' => 'Part-Time'],
            ['name' => 'Attachment'],
            ['name' => 'Contract'],
            ['name' => 'Other'],
        ]);

//        EmploymentType::create([
//            'name'  => 'Pensionable',
//        ]);
//        EmploymentType::create([
//            'name'  => 'Full-Time',
//        ]);
//        EmploymentType::create([
//            'name'  => 'Part-Time',
//        ]);
//        EmploymentType::create([
//            'name'  => 'Attachment',
//        ]);
//        EmploymentType::create([
//            'name'  => 'Contract',
//        ]);
//        EmploymentType::create([
//            'name'  => 'Other',
//        ]);
    }

    public function employmentStatuses(): void
    {
        EmploymentStatus::truncate();

        EmploymentStatus::insert([
            ['name' => 'Active'],
            ['name' => 'On Leave'],
            ['name' => 'Resigned'],
            ['name' => 'Retired'],
            ['name' => 'Suspended'],
        ]);
    }

    public function jobTitles(): void
    {
        JobTitle::truncate();

        JobTitle::insert([
            ['name' => 'Teacher'],
            ['name' => 'Accountant'],
            ['name' => 'Librarian'],
            ['name' => 'Lab Technician'],
            ['name' => 'Driver'],
            ['name' => 'Carpenter'],
            ['name' => 'Cleaner'],
            ['name' => 'Cook'],
            ['name' => 'Guard'],
            ['name' => 'Electrician'],
        ]);
    }

    public function specialisations(): void
    {
        Specialization::truncate();

        Specialization::insert([
            ['name' => 'STEM'],
            ['name' => 'Art'],
            ['name' => 'Sciences'],
            ['name' => 'Languages'],
            ['name' => 'Religious Education'],
        ]);
    }

    public function qualificationTypes(): void
    {
        QualificationType::truncate();

        QualificationType::insert([
            ['name' => 'Degree'],
            ['name' => 'Diploma'],
            ['name' => 'Certificate'],
        ]);
    }

    public function salaryGrades(): void
    {
        SalaryGrade::truncate();

        SalaryGrade::insert([
            ['name' => 'D5'],
            ['name' => 'D4'],
            ['name' => 'D3'],
            ['name' => 'D2'],
            ['name' => 'D1'],
            ['name' => 'C5'],
            ['name' => 'C4'],
            ['name' => 'C3'],
            ['name' => 'C2'],
            ['name' => 'C1'],
            ['name' => 'B1'],
        ]);
    }

    public function salaryScales(): void
    {
        SalaryScale::truncate();

        $grades = SalaryGrade::all();

        $scales = [
            ['name' => 'T-Scale 15', 'salary_grade_id' => $grades->firstWhere('name', '=', 'D5')->id],
            ['name' => 'T-Scale 14', 'salary_grade_id' => $grades->firstWhere('name', '=', 'D4')->id],
            ['name' => 'T-Scale 13', 'salary_grade_id' => $grades->firstWhere('name', '=', 'D3')->id],
            ['name' => 'T-Scale 12', 'salary_grade_id' => $grades->firstWhere('name', '=', 'D2')->id],
            ['name' => 'T-Scale 11', 'salary_grade_id' => $grades->firstWhere('name', '=', 'D1')->id],
            ['name' => 'T-Scale 10', 'salary_grade_id' => $grades->firstWhere('name', '=', 'C5')->id],
            ['name' => 'T-Scale 9', 'salary_grade_id' => $grades->firstWhere('name', '=', 'C4')->id],
            ['name' => 'T-Scale 8', 'salary_grade_id' => $grades->firstWhere('name', '=', 'C3')->id],
            ['name' => 'T-Scale 7', 'salary_grade_id' => $grades->firstWhere('name', '=', 'C2')->id],
            ['name' => 'T-Scale 6', 'salary_grade_id' => $grades->firstWhere('name', '=', 'C1')->id],
            ['name' => 'T-Scale 5', 'salary_grade_id' => $grades->firstWhere('name', '=', 'B1')->id],
        ];

        foreach($scales as $index => $scale) {

            SalaryScale::create([
                'name'  => $scale['name'],
                'salary_grade_id' => $scale['salary_grade_id'],
            ]);
        }
    }

    public function teacherTitles(): void
    {
        TeacherTitle::truncate();
    }

    public function institution(): void
    {
        Institution::truncate();

        Institution::create([
            'name' => 'SHARIFF NASSIR GIRLS SECONDARY SCHOOL',
            'email' => 'info@shariffnassirgirls.co.ke',
            'phone' => '254776160927',
            'country' => 'KENYA',
            'state' => 'MOMBASA',
            'city' => 'MOMBASA',
            'physical_address' => 'Kisauni Rd, Off Sheikh Abdullas Rd, Mombasa',
            'postal_address' => '86716-80100',
            'tax_identification_pin' => '',
            'mission' => 'To empower students to become productive members of the society by providing a conducive environment that will nurture them academically, socially and emotionally.',
            'vision' => 'To be a leading school in the provision of quality and holistic education for self-actualization.',
//            'x_profile' => '',
//            'fb_profile' => '',
//            'ig_profile' => '',
//            'tiktok_profile' => '',
//            'youtube_profile' => '',
        ]);
    }
}
