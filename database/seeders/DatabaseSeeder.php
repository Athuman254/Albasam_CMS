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
use App\Models\TeacherTitle;
use App\Models\User;
use App\Models\Website\Customisation;
use App\Models\Website\Menu;
use App\Models\Website\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
        
        $this->divisions();
        
        $this->streams();
        
        $this->ranks();
        
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
        
        $this->customisations();
        
        $this->pages();
        
        $this->menus();
        
        User::create([
            'name'      => 'John Doe',
            'username'  => 'admin',
            'email'     => 'admin@app.com',
            'phone'     => '0712345678',
            'password'  => Hash::make('admin@!2025'),
            'is_admin' => true,
        ]);
        
        $this->call(LaratrustSeeder::class);
        
        Schema::enableForeignKeyConstraints();
    }
    
    public function departments(): void
    {
        Department::truncate();
        
        Department::insert([
            ['name' => 'Science', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Languages', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Library', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function genders(): void
    {
        Gender::truncate();
        
        Gender::insert([
            ['name' => 'Male', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Female', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function religions(): void
    {
        Religion::truncate();
        
        Religion::insert([
            ['name' => 'Christian', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Islam', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hindu', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function relationships(): void
    {
        Relationship::truncate();
        
        Relationship::insert([
            ['name' => 'Father', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mother', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Husband', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wife', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Brother', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sister', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Uncle', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aunt', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grandparent', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function divisions(): void
    {
        Division::truncate();
        
        Division::insert([
            ['name' => 'High School', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function streams(): void
    {
        Stream::truncate();
        
        Stream::insert([
            ['name' => 'Aberdare', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Satima', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kinangop', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function ranks(): void
    {
        Rank::truncate();
        $divisionId = Division::first()->id;
        $streams = Stream::all();
        
        Rank::insert([
            [
                'name' => 'Form 1',
                'division_id' => $divisionId,
                'stream_id' => $streams->firstWhere('name', '=', 'Aberdare')->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Form 1',
                'division_id' => $divisionId,
                'stream_id' => $streams->firstWhere('name', '=', 'Satima')->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Form 1',
                'division_id' => $divisionId,
                'stream_id' => $streams->firstWhere('name', '=', 'Kinangop')->id,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
    
    public function maritalStatuses(): void
    {
        MaritalStatus::truncate();
        
        MaritalStatus::insert([
            ['name' => 'Married', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Single', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Single-Parent', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Divorced', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function honorifics(): void
    {
        Honorific::truncate();
        
        Honorific::insert([
            ['name' => 'Mr.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mrs.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Md.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Prof.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lec.', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function employmentTypes(): void
    {
        EmploymentType::truncate();
        
        EmploymentType::insert([
            ['name' => 'Pensionable', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Full-Time', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Part-Time', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Internship', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Contract', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function employmentStatuses(): void
    {
        EmploymentStatus::truncate();
        
        EmploymentStatus::insert([
            ['name' => 'Active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'On Leave', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Resigned', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Retired', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Suspended', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function jobTitles(): void
    {
        JobTitle::truncate();
        
        JobTitle::insert([
            ['name' => 'Teacher', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Accountant', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Librarian', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lab Technician', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Driver', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Carpenter', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cleaner', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cook', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Guard', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Electrician', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function specialisations(): void
    {
        Specialization::truncate();
        
        Specialization::insert([
            ['name' => 'Sciences', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Languages', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Humanities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Religious Education', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function qualificationTypes(): void
    {
        QualificationType::truncate();
        
        QualificationType::insert([
            ['name' => 'Degree', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Diploma', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Certificate', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function salaryGrades(): void
    {
        SalaryGrade::truncate();
        
        SalaryGrade::insert([
            ['name' => 'D5', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'D4', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'D3', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'D2', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'D1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'C5', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'C4', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'C3', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'C2', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'C1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'B1', 'created_at' => now(), 'updated_at' => now()],
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
            'physical_address' => 'WMXC+PGR, Kisauni Rd, Off Sheik Abdullas Rd, Mombasa',
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
    
    public function customisations(): void
    {
        Customisation::truncate();
        
        Customisation::create([
            'primary_color' => '#25615a',
        ]);
    }
    
    public function pages(): void
    {
        Page::insert([
            ['title' => 'Home', 'slug' => '/', 'published' => true, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'About Us', 'slug' => 'about-us', 'published' => true, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'Contact Us', 'slug' => 'contact-us', 'published' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    
    public function menus(): void
    {
        Menu::truncate();
        
        $pages = Page::all();
        foreach ($pages as $page) {
            Menu::create([
                'page_id' => $page->id,
                'title' => $page->title,
                'type' => Menu::TYPE_PAGE,
                'order' => Menu::max('order') + 1,
            ]);
        }
    }
}
