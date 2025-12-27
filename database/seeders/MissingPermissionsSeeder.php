<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class MissingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Fee Management Permissions
            ['name' => 'add-fees', 'display_name' => 'Add Fees', 'description' => 'Create new fee records'],
            ['name' => 'edit-fees', 'display_name' => 'Edit Fees', 'description' => 'Modify existing fee records'],
            ['name' => 'delete-fees', 'display_name' => 'Delete Fees', 'description' => 'Remove fee records'],
            ['name' => 'view-fees', 'display_name' => 'View Fees', 'description' => 'View fee records'],
            ['name' => 'access-fee-structures-workspace', 'display_name' => 'Access Fee Structures', 'description' => 'Access fee structures workspace'],
            ['name' => 'add-fee-structures', 'display_name' => 'Add Fee Structures', 'description' => 'Create new fee structures'],
            ['name' => 'edit-fee-structures', 'display_name' => 'Edit Fee Structures', 'description' => 'Modify fee structures'],
            ['name' => 'delete-fee-structures', 'display_name' => 'Delete Fee Structures', 'description' => 'Remove fee structures'],
            ['name' => 'manage-fee-payments', 'display_name' => 'Manage Fee Payments', 'description' => 'Verify and manage fee payments'],

            // Exam Management Permissions
            ['name' => 'access-exams-workspace', 'display_name' => 'Access Exams Workspace', 'description' => 'Access exam management workspace'],
            ['name' => 'add-exams', 'display_name' => 'Add Exams', 'description' => 'Create new exams'],
            ['name' => 'edit-exams', 'display_name' => 'Edit Exams', 'description' => 'Modify existing exams'],
            ['name' => 'delete-exams', 'display_name' => 'Delete Exams', 'description' => 'Remove exams'],
            ['name' => 'view-exams', 'display_name' => 'View Exams', 'description' => 'View exam records'],
            ['name' => 'upload-exam-results', 'display_name' => 'Upload Exam Results', 'description' => 'Upload student exam results'],
            ['name' => 'approve-exam-results', 'display_name' => 'Approve Exam Results', 'description' => 'Approve submitted exam results'],
            ['name' => 'view-exam-results', 'display_name' => 'View Exam Results', 'description' => 'View exam results and reports'],
            ['name' => 'manage-grading-scales', 'display_name' => 'Manage Grading Scales', 'description' => 'Manage exam grading scales'],
            ['name' => 'generate-report-cards', 'display_name' => 'Generate Report Cards', 'description' => 'Generate student academic report cards'],

            // Payroll Management Permissions
            ['name' => 'access-payroll-workspace', 'display_name' => 'Access Payroll Workspace', 'description' => 'Access payroll management workspace'],
            ['name' => 'manage-payroll', 'display_name' => 'Manage Payroll', 'description' => 'Full payroll management access'],
            ['name' => 'run-payroll', 'display_name' => 'Run Payroll', 'description' => 'Execute payroll processing'],
            ['name' => 'view-payroll', 'display_name' => 'View Payroll', 'description' => 'View payroll records'],
            ['name' => 'edit-payroll-adjustments', 'display_name' => 'Edit Payroll Adjustments', 'description' => 'Modify payroll adjustments'],
            ['name' => 'approve-payroll', 'display_name' => 'Approve Payroll', 'description' => 'Approve payroll runs'],
            ['name' => 'access-payroll-reports', 'display_name' => 'Access Payroll Reports', 'description' => 'View detailed payroll reports'],
            ['name' => 'manage-allowances', 'display_name' => 'Manage Allowances', 'description' => 'Manage employee allowances'],
            ['name' => 'manage-deductions', 'display_name' => 'Manage Deductions', 'description' => 'Manage employee deductions'],

            // HR & Employee Management
            ['name' => 'access-hr-dashboard', 'display_name' => 'Access HR Dashboard', 'description' => 'Access HR specific dashboard'],
            ['name' => 'manage-leave-requests', 'display_name' => 'Manage Leave Requests', 'description' => 'Manage staff leave applications'],
            ['name' => 'approve-leave-requests', 'display_name' => 'Approve Leave Requests', 'description' => 'Approve/Reject staff leave requests'],

            // Finance Management (Extended)
            ['name' => 'access-financial-reports', 'display_name' => 'Access Financial Reports', 'description' => 'View financial and audit reports'],
            ['name' => 'manage-bank-accounts', 'display_name' => 'Manage Bank Accounts', 'description' => 'Manage institution bank accounts'],
            ['name' => 'manage-expenses', 'display_name' => 'Manage Expenses', 'description' => 'Track and manage institution expenses'],

            // Academic & Attendance
            ['name' => 'manage-attendance', 'display_name' => 'Manage Attendance', 'description' => 'Manage student and staff attendance'],
            ['name' => 'export-attendance', 'display_name' => 'Export Attendance', 'description' => 'Export attendance records'],
            ['name' => 'manage-gradings', 'display_name' => 'Manage Gradings', 'description' => 'Manage grading schemes and scales'],

            // Reports Permissions
            ['name' => 'access-reports', 'display_name' => 'Access Reports', 'description' => 'Access reports workspace'],
            ['name' => 'generate-reports', 'display_name' => 'Generate Reports', 'description' => 'Generate system reports'],
            ['name' => 'export-reports', 'display_name' => 'Export Reports', 'description' => 'Export reports to PDF/Excel'],
            ['name' => 'view-reports', 'display_name' => 'View Reports', 'description' => 'View generated reports'],

            // Website Management Permissions
            ['name' => 'access-website-workspace', 'display_name' => 'Access Website Workspace', 'description' => 'Access website management'],
            ['name' => 'manage-website-content', 'display_name' => 'Manage Website Content', 'description' => 'Full website content management'],
            ['name' => 'edit-website-pages', 'display_name' => 'Edit Website Pages', 'description' => 'Modify website pages'],
            ['name' => 'delete-website-pages', 'display_name' => 'Delete Website Pages', 'description' => 'Remove website pages'],
            ['name' => 'manage-website-menus', 'display_name' => 'Manage Website Menus', 'description' => 'Manage website navigation menus'],
            ['name' => 'manage-website-seo', 'display_name' => 'Manage Website SEO', 'description' => 'Manage SEO settings'],
            ['name' => 'manage-blogs', 'display_name' => 'Manage Blogs', 'description' => 'Manage blog posts'],
            ['name' => 'manage-careers', 'display_name' => 'Manage Careers', 'description' => 'Manage career postings'],

            // Guardian Permissions
            ['name' => 'access-guardians-workspace', 'display_name' => 'Access Guardians Workspace', 'description' => 'Access guardian management'],
            ['name' => 'add-guardians', 'display_name' => 'Add Guardians', 'description' => 'Create new guardian records'],
            ['name' => 'edit-guardians', 'display_name' => 'Edit Guardians', 'description' => 'Modify guardian records'],
            ['name' => 'delete-guardians', 'display_name' => 'Delete Guardians', 'description' => 'Remove guardian records'],
            ['name' => 'view-guardians', 'display_name' => 'View Guardians', 'description' => 'View guardian records'],

            // System Management
            ['name' => 'access-activity-logs', 'display_name' => 'Access Activity Logs', 'description' => 'View system activity logs'],
            ['name' => 'manage-backups', 'display_name' => 'Manage Backups', 'description' => 'Manager system backups'],
            ['name' => 'manage-settings', 'display_name' => 'Manage System Settings', 'description' => 'Manage global institution settings'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        $this->command->info('Missing permissions added successfully!');
        $this->command->info('Total updated permissions: ' . count($permissions));
    }
}
