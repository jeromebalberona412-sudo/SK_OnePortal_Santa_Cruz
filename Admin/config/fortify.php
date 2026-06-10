<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    'limiters' => [
        'login' => 'login',
        'two-factor' => null,
    ],
    'views' => true,
    'features' => [
        // Registration disabled for admin-only application
        // Features::registration(),
        // Password reset handled by custom Authentication module
        // Features::resetPasswords(),
        // Email verification disabled
        // Features::emailVerification(),
        // Features::updateProfileInformation(),
        // Features::updatePasswords(),
    ],
];
