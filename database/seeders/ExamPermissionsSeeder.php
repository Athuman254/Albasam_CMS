<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ExamPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create missing exam permissions
        $permissions = [
            'manage-exams' => 'Can manage exams (create, edit, delete)',
            'enroll-students' => 'Can enroll students in exams',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web', 'description' => $description]
            );
        }

        $this->command->info('Exam permissions created successfully!');
    }
}
