<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class AddEditApprovedMarksPermissionSeeder extends Seeder
{
    public function run()
    {
        // 1. Create the permission
        $permission = Permission::firstOrCreate([
            'name' => 'edit-approved-marks',
            'display_name' => 'Edit Approved Marks',
            'description' => 'Edit Approved Marks Exam-Management',
            'code' => 'Exam-Management'
        ]);

        $this->command->info("Permission 'edit-approved-marks' ensured.");

        // 2. Assign to Administrator role
        $adminRole = Role::where('name', 'administrator')->first();
        if ($adminRole) {
            if (!$adminRole->hasPermission('edit-approved-marks')) {
                $adminRole->givePermission($permission);
                $this->command->info("Permission attached to 'administrator' role.");
            } else {
                $this->command->info("Administrator already has this permission.");
            }
        }
    }
}
