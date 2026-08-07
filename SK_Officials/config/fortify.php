<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'lowercase_usernames' => true,
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    'limiters' => [
        'login' => 'sk-official-login',
        'two-factor' => null,
    ],
    'views' => true,
    'redirects' => [
        'login' => '/dashboard',
        'email-verification' => '/email/verified-success',
    ],
    'features' => [
        Features::emailVerification(),
    ],
];
