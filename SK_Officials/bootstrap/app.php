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
        $middleware->web(append: [
            \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->alias([
            'sk_official.access' => \App\Modules\Authentication\Middleware\EnsureSkOfficialAccess::class,
            'trusted.device' => \App\Modules\Authentication\Middleware\EnsureTrustedDevice::class,
            'single.session' => \App\Modules\Authentication\Middleware\EnsureSingleSession::class,
            'turnstile' => \App\Modules\Authentication\Middleware\VerifyTurnstile::class,
            'must.change.password' => \App\Modules\Authentication\Middleware\EnsurePasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
