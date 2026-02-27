<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Modules\Shared\Middleware\ForceHttps::class);
        $middleware->append(\App\Modules\Shared\Middleware\SecurityHeaders::class);
        $middleware->alias([
            'ensure2fa' => \App\Modules\Authentication\Middleware\EnsureTwoFactorEnabled::class,
            'role' => \App\Modules\Shared\Middleware\EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
