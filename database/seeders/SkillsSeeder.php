<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Skill;

class SkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = Subject::all();

        $skillsBySubject = [
            'Mathematics' => [
                'Numbers & Operations',
                'Algebra',
                'Geometry',
                'Measurement',
                'Data Handling'
            ],
            'English' => [
                'Listening & Speaking',
                'Reading Comprehension',
                'Grammar & Usage',
                'Writing & Composition',
                'Vocabulary'
            ],
            'Kiswahili' => [
                'Kusikiliza na Kuzungumza',
                'Ufahamu',
                'Sarufi',
                'Kuandika',
                'Msamiati'
            ],
            'Science' => [
                'Scientific Inquiry',
                'Life Science',
                'Physical Science',
                'Earth & Space',
                'Health Education'
            ],
            'Social Studies' => [
                'History & Government',
                'Geography',
                'Citizenship',
                'Culture & Society',
                'Economics'
            ],
            'CRE' => [
                'Biblical Knowledge',
                'Christian Values',
                'Moral Development',
                'Church History',
                'Application to Life'
            ],
            'Creative Arts' => [
                'Drawing & Painting',
                'Music & Dance',
                'Crafts',
                'Drama',
                'Appreciation'
            ],
            'Agriculture' => [
                'Crop Production',
                'Animal Production',
                'Soil Science',
                'Agricultural Economics',
                'Tools & Equipment'
            ]
        ];

        foreach ($subjects as $subject) {
            if (isset($skillsBySubject[$subject->name])) {
                foreach ($skillsBySubject[$subject->name] as $skillName) {
                    Skill::firstOrCreate(
                        [
                            'name' => $skillName,
                            'subject_id' => $subject->id
                        ],
                        [
                            'description' => $skillName . ' skills for ' . $subject->name,
                            'is_active' => true
                        ]
                    );
                }
            }
        }

        $this->command->info('✅ Skills seeded successfully!');
    }
}
