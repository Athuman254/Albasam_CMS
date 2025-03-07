<?php

return [
    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'modules' => [
        'Configurations' => [
            'institution-workspace' => 'a',
            'divisions-workspace' => 'a',
            'divisions' => 'c,e,d',
            'streams-workspace' => 'a',
            'streams' => 'c,e,d',
            'subjects-workspace' => 'a',
            'subjects' => 'c,e,d',
        ],
        'User Management' => [
            'users-workspace' => 'a',
            'users' => 'c,e,r,d',
            'roles-workspace' => 'a',
            'roles' => 'c,e,d',
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
