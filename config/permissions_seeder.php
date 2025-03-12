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
