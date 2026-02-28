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
        $middleware->prependToGroup('web', \App\Modules\Shared\Middleware\EnsureConsistentHost::class);

        $middleware->alias([
            'sk_fed.access' => \App\Modules\Authentication\Middleware\EnsureSkFedAccess::class,
            'trusted.device' => \App\Modules\Authentication\Middleware\EnsureTrustedDevice::class,
            'single.session' => \App\Modules\Authentication\Middleware\EnsureSingleSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
