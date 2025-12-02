<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class NewReportsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'view-all-students-report',
                'display_name' => 'View All Students Report',
                'description' => 'Can view and export all students report'
            ],
            [
                'name' => 'view-all-staff-report',
                'display_name' => 'View All Staff Report',
                'description' => 'Can view and export all staff report'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Helper to safely add permissions
        $addPermissions = function ($roleName, $permissionNames) {
            $role = Role::where('name', $roleName)->first();
            if (!$role) return;

            $currentIds = $role->permissions()->pluck('id')->toArray();
            $newIds = Permission::whereIn('name', $permissionNames)->pluck('id')->toArray();

            $role->syncPermissions(array_unique(array_merge($currentIds, $newIds)));
        };

        $addPermissions('admin', ['view-all-students-report', 'view-all-staff-report']);
        $addPermissions('hr-manager', ['view-all-staff-report']);
        $addPermissions('academic-coordinator', ['view-all-students-report']);
        $addPermissions('principal', ['view-all-students-report', 'view-all-staff-report']);

        $this->command->info('New report permissions created and assigned.');
    }
}
