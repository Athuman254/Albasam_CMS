<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class StaffAttendancePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'mark-staff-attendance',
                'display_name' => 'Mark Staff Attendance',
                'description' => 'Mark own attendance (clock in/out)'
            ],
            [
                'name' => 'view-staff-attendance-reports',
                'display_name' => 'View Staff Attendance Reports',
                'description' => 'View staff attendance reports'
            ],
            [
                'name' => 'export-staff-attendance-reports',
                'display_name' => 'Export Staff Attendance Reports',
                'description' => 'Export staff attendance reports to Excel/PDF'
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        $this->command->info('Staff attendance permissions created successfully!');
    }
}
