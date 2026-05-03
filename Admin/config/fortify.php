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
        // Password reset disabled (login-only requirement)
        // Features::resetPasswords(),
        // Email verification disabled
        // Features::emailVerification(),
        // Password update disabled for now
        // Features::updateProfileInformation(),
        // Features::updatePasswords(),
        // Two-Factor Authentication ENABLED
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => false,
        ]),
    ],
];
