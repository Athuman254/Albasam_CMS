<?php

return [
    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'modules' => [
        'Admission-Management' => [
            'admissions-workspace' => 'a',
            'admissions' => 'c,e,d',
            'admission-applications' => 'a,r,e,d',
            'students-workspace' => 'a',
            'students' => 'r,c,e,d',
        ],
        'Class-Management' => [
            'class-workspace' => 'a',
            'classes' => 'r,c,e,d',
            'calendar' => 'a',
            'time-table' => 'a',
            'lessons' => 'c,e,d',
            'attendance-workspace' => 'a',
            'attendance-report' => 'a'
        ],
        'Employee-Management' => [
            'employee-workspace' => 'a',
            'employees' => 'r,c,e,d',
            'teacher-workspace' => 'a',
            'teachers' => 'r,c,e,d',
        ],
        'Sms-Management' => [
            'bulk-sms' => 'a',
            'sms-outbox' => 'a',
        ],
        'Settings' => [
            'institution-workspace' => 'a',
            'institution' => 'c,e',
            'divisions-workspace' => 'a',
            'divisions' => 'c,e,d',
            'streams-workspace' => 'a',
            'streams' => 'c,e,d',
            'subjects-workspace' => 'a',
            'subjects' => 'c,e,d',
        ],
        'User-Management' => [
            'users-workspace' => 'a',
            'users' => 'c,e,r,d',
            'roles-workspace' => 'a',
            'roles' => 'c,e,d',
        ],
        'Exam-Management' => [
            'exams-workspace' => 'a',
            'exams' => 'c,e,d,r',
            'exam-results' => 'c,e,r',
            'manage-exams' => 'a',
            'enroll-students' => 'a',
            'upload-exam-results' => 'a',
            'approve-exam-results' => 'a',
            'approved-marks' => 'e',
        ],
        'Fee-Management' => [
            'fees-workspace' => 'a',
            'fees' => 'c,e,d,r',
            'fee-structures-workspace' => 'a',
            'fee-structures' => 'c,e,d,r',
            'fee-payments' => 'c,e,r',
        ],
        'Finance-Management' => [
            'payroll-workspace' => 'a',
            'payroll' => 'c,e,d,r',
            'manage-payroll' => 'a',
            'approve-payroll' => 'a',
            'run-payroll' => 'a',
            'edit-payroll-adjustments' => 'a',
        ],
        'Report-Management' => [
            'reports-workspace' => 'a',
            'reports' => 'r',
            'promotions-report' => 'a',
            'staff-attendance-reports' => 'r',
            'all-students-report' => 'r',
            'all-staff-report' => 'r',
            'student-ids' => 'a,r',
        ],
        'Timetable-Management' => [
            'timetable-workspace' => 'a',
            'periods' => 'c,e,d,r',
            'allocations' => 'c,e,d,r',
            'constraints' => 'c,e,d,r',
            'class-timetables' => 'r',
            'teacher-timetables' => 'r',
            'timetable-generation' => 'c,e,d,r',
            'publish-timetable' => 'a',
            'view-timetable' => 'a',
            'create-timetable' => 'a',
            'edit-timetable' => 'a',
            'delete-timetable' => 'a',
        ],
        'LMS-Management' => [
            'lms-workspace' => 'a',
            'lesson-materials' => 'c,e,d,r',
            'assignments' => 'c,e,d,r',
            'assignment-submissions' => 'r,e',
            'revision-tools' => 'c,e,d,r',
            'online-classes' => 'c,e,d,r',
        ],
        'Website-Management' => [
            'pages-workspace' => 'a',
            'pages' => 'c,e,d',
            'page-sections' => 'c,e,d'
        ],
    ],

    'permissions_map' => [
        'a' => 'access',
        'c' => 'add',
        'e' => 'edit',
        'r' => 'view',
        'd' => 'delete',
    ],
];
