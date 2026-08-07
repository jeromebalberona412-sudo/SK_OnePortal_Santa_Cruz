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
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->command('announcements:purge-archived')->daily();
        $schedule->command('sports-programs:purge-archived')->daily();
        $schedule->command('archive:purge-expired')->daily();
        $schedule->command('kabataan:archive-aged-out')->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->alias([
            'sk_official.access' => \App\Modules\Authentication\Middleware\EnsureSkOfficialAccess::class,
            'trusted.device' => \App\Modules\Authentication\Middleware\EnsureTrustedDevice::class,
            'single.session' => \App\Modules\Authentication\Middleware\EnsureSingleSession::class,
            'turnstile' => \App\Modules\Authentication\Middleware\VerifyTurnstile::class,
            'altcha' => \App\Modules\Authentication\Middleware\VerifyAltcha::class,
            'must.change.password' => \App\Modules\Authentication\Middleware\EnsurePasswordChanged::class,
        ]);

        // Ensure proper middleware execution order
        // Auth middleware must run before our custom middleware
        $middleware->priority([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
            \App\Modules\Authentication\Middleware\EnsureSingleSession::class,
            \App\Modules\Authentication\Middleware\EnsureSkOfficialAccess::class,
            \App\Modules\Authentication\Middleware\EnsurePasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
