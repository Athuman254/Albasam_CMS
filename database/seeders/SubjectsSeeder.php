<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                'name' => 'Mathematics',
                'code' => 'MAT',
                'group' => Subject::SCIENCE,
                'activated' => true,
            ],
            [
                'name' => 'English',
                'code' => 'ENG',
                'group' => Subject::LANGUAGES,
                'activated' => true,
            ],
            [
                'name' => 'Kiswahili',
                'code' => 'KIS',
                'group' => Subject::LANGUAGES,
                'activated' => true,
            ],
            [
                'name' => 'Science',
                'code' => 'SCI',
                'group' => Subject::SCIENCE,
                'activated' => true,
            ],
            [
                'name' => 'Social Studies',
                'code' => 'SST',
                'group' => Subject::HUMANITIES,
                'activated' => true,
            ],
            [
                'name' => 'Christian Religious Education',
                'code' => 'CRE',
                'group' => Subject::HUMANITIES,
                'activated' => true,
            ],
            [
                'name' => 'Creative Arts',
                'code' => 'CAP',
                'group' => Subject::CREATIVE_ARTS,
                'activated' => true,
            ],
            [
                'name' => 'Agriculture',
                'code' => 'AGR',
                'group' => Subject::APPLIED_SCIENCE,
                'activated' => true,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['code' => $subject['code']],
                $subject
            );
        }

        $this->command->info('✅ Successfully created ' . count($subjects) . ' subjects!');
    }
}
