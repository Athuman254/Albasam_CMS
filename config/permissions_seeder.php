<?php

return [
    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'modules' => [
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
