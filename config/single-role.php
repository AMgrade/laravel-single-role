<?php

declare(strict_types=1);

use App\Models\User;

return [
    'models' => [
        'user' => User::class,
    ],

    'tables' => [
        'users' => 'users',
        'roles' => 'roles',
        'permissions' => 'permissions',

        // Pivot tables.
        'permission_role' => 'permission_role',
        'permission_user' => 'permission_user',
    ],

    // Delimiter for passing roles and permissions as string.
    'delimiter' => '|',
];
