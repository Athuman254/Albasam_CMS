<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define roles
        $roles = [
            [
                'name' => 'administrator',
                'display_name' => 'Administrator',
                'description' => 'Full system access - can manage all aspects of the system'
            ],
            [
                'name' => 'accountant',
                'display_name' => 'Accountant/Bursar',
                'description' => 'Fee and financial management - handles all fee-related operations'
            ],
            [
                'name' => 'academic-coordinator',
                'display_name' => 'Academic Coordinator',
                'description' => 'Academic operations management - manages students, exams, and classes'
            ],
            [
                'name' => 'hr-manager',
                'display_name' => 'HR Manager',
                'description' => 'Employee and payroll management - handles staff and payroll operations'
            ],
            [
                'name' => 'receptionist',
                'display_name' => 'Receptionist',
                'description' => 'Admissions and front desk - handles student admissions and basic info'
            ],
            [
                'name' => 'librarian',
                'display_name' => 'Librarian',
                'description' => 'Library management - manages library resources and student access'
            ],
            [
                'name' => 'principal',
                'display_name' => 'Principal/Director',
                'description' => 'Reports and oversight - view-only access to reports and statistics'
            ],
            [
                'name' => 'teacher',
                'display_name' => 'Teacher',
                'description' => 'Teaching staff - manages classes, attendance, and exams'
            ],
        ];

        // Create or update roles
        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('Roles created successfully!');

        // Assign permissions to roles
        $this->assignPermissions();

        $this->command->info('Permissions assigned to roles successfully!');
    }

    /**
     * Assign permissions to each role
     */
    private function assignPermissions(): void
    {
        // Admin gets ALL permissions
        $admin = Role::where('name', 'administrator')->first();
        if ($admin) {
            $admin->syncPermissions(Permission::all());
            $this->command->info('Admin role: ALL permissions assigned');
        }

        // Accountant/Bursar - Fee Management Focus
        $accountant = Role::where('name', 'accountant')->first();
        if ($accountant) {
            $accountantPermissions = Permission::whereIn('name', [
                // Fee Management - Full Access
                'access-fees-workspace',
                'add-fees',
                'edit-fees',
                'delete-fees',
                'view-fees',
                'access-fee-structures-workspace',
                'add-fee-structures',
                'edit-fee-structures',
                'delete-fee-structures',
                'manage-fee-payments',

                // Students (View only for fee purposes)
                'view-students',
                'access-students-workspace',

                // Reports
                'access-reports',
                'view-reports',
                'generate-reports',
                'export-reports',

                // SMS (for fee reminders)
                'access-bulk-sms',
                'access-sms-outbox',

                // Staff Attendance
                'mark-staff-attendance',
            ])->pluck('id');

            $accountant->syncPermissions($accountantPermissions);
            $this->command->info('Accountant role: Fee management permissions assigned');
        }

        // Academic Coordinator - Academic Operations Focus
        $academic = Role::where('name', 'academic-coordinator')->first();
        if ($academic) {
            $academicPermissions = Permission::whereIn('name', [
                // Admissions
                'access-admissions-workspace',
                'add-admissions',
                'edit-admissions',
                'delete-admissions',

                // Students
                'access-students-workspace',
                'add-students',
                'edit-students',
                'view-students',
                'delete-students',

                // Classes
                'access-class-workspace',
                'add-class',
                'edit-class',
                'delete-class',

                // Divisions & Streams
                'access-divisions-workspace',
                'add-divisions',
                'edit-divisions',
                'delete-divisions',
                'access-streams-workspace',
                'add-streams',
                'edit-streams',
                'delete-streams',

                // Subjects
                'access-subjects-workspace',
                'add-subjects',
                'edit-subjects',
                'delete-subjects',

                // Exams - Full Access
                'access-exams-workspace',
                'add-exams',
                'edit-exams',
                'delete-exams',
                'view-exams',
                'upload-exam-results',
                'approve-exam-results',
                'view-exam-results',

                // Attendance
                'access-attendance-workspace',
                'access-attendance-report',

                // Timetable - Full Access
                'access-time-table',
                'access-timetable-workspace',
                'create-timetable',
                'edit-timetable',
                'delete-timetable',
                'view-timetable',
                'publish-timetable',

                // Reports
                'access-reports',
                'view-reports',
                'generate-reports',
                'export-reports',

                // SMS
                'access-bulk-sms',
                'access-sms-outbox',

                // Staff Attendance
                'mark-staff-attendance',
            ])->pluck('id');

            $academic->syncPermissions($academicPermissions);
            $this->command->info('Academic Coordinator role: Academic permissions assigned');
        }

        // HR Manager - Employee & Payroll Focus
        $hr = Role::where('name', 'hr-manager')->first();
        if ($hr) {
            $hrPermissions = Permission::whereIn('name', [
                // Employees
                'access-employee-workspace',
                'add-employee',
                'edit-employee',
                'view-employees',
                'delete-employee',

                // Teachers
                'access-teacher-workspace',
                'add-teacher',
                'edit-teacher',
                'view-teachers',
                'delete-teacher',

                // Payroll - Full Access
                'access-payroll-workspace',
                'manage-payroll',
                'run-payroll',
                'view-payroll',
                'edit-payroll-adjustments',
                'approve-payroll',

                // Reports
                'access-reports',
                'view-reports',
                'generate-reports',
                'export-reports',

                // Staff Attendance
                'view-staff-attendance-reports',
                'export-staff-attendance-reports',
                'mark-staff-attendance',
            ])->pluck('id');

            $hr->syncPermissions($hrPermissions);
            $this->command->info('HR Manager role: Employee & payroll permissions assigned');
        }

        // Receptionist - Admissions & Basic Info Focus
        $receptionist = Role::where('name', 'receptionist')->first();
        if ($receptionist) {
            $receptionistPermissions = Permission::whereIn('name', [
                // Admissions
                'access-admissions-workspace',
                'add-admissions',
                'edit-admissions',

                // Students (View only)
                'view-students',
                'access-students-workspace',

                // Guardians - Full Access
                'access-guardians-workspace',
                'add-guardians',
                'edit-guardians',
                'view-guardians',

                // Staff Attendance
                'mark-staff-attendance',
            ])->pluck('id');

            $receptionist->syncPermissions($receptionistPermissions);
            $this->command->info('Receptionist role: Admissions permissions assigned');
        }

        // Librarian - Library Focus (minimal for now)
        $librarian = Role::where('name', 'librarian')->first();
        if ($librarian) {
            $librarianPermissions = Permission::whereIn('name', [
                // Students (View only)
                'view-students',
                'access-students-workspace',

                // Library (when implemented)
                // 'access-library-workspace',
                // 'manage-library',

                // Staff Attendance
                'mark-staff-attendance',
            ])->pluck('id');

            $librarian->syncPermissions($librarianPermissions);
            $this->command->info('Librarian role: Basic permissions assigned');
        }

        // Principal - Reports & Overview Focus (View-only)
        $principal = Role::where('name', 'principal')->first();
        if ($principal) {
            $principalPermissions = Permission::whereIn('name', [
                // View-only access
                'view-students',
                'view-employees',
                'view-teachers',

                // Reports
                'access-reports',
                'access-attendance-report',

                // Dashboard access
                'access-students-workspace',
                'access-employee-workspace',
                'access-exams-workspace',
                'access-fees-workspace',

                // Staff Attendance
                'mark-staff-attendance',
            ])->pluck('id');

            $principal->syncPermissions($principalPermissions);
            $this->command->info('Principal role: View-only permissions assigned');
        }

        // Teacher - Teaching Focus
        $teacher = Role::where('name', 'teacher')->first();
        if ($teacher) {
            $teacherPermissions = Permission::whereIn('name', [
                // Attendance
                'access-attendance-workspace',

                // Exams
                'access-exams-workspace',

                // Students (View only)
                'view-students',

                // Timetable
                'access-time-table',

                // Staff Attendance
                'mark-staff-attendance',
            ])->pluck('id');

            $teacher->syncPermissions($teacherPermissions);
            $this->command->info('Teacher role: Teaching permissions assigned');
        }
    }
}
