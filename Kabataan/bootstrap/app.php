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
        $middleware->alias([
            'kabataan' => \App\Http\Middleware\EnsureKabataanUser::class,
            'kabataan.view_only_guard' => \App\Http\Middleware\PreventArchivedKabataanMutations::class,
            'kk_profiling.update_required' => \App\Http\Middleware\EnsureKkProfilingUpdated::class,
        ]);

        $middleware->appendToGroup('auth', [
            'kabataan',
            'kabataan.view_only_guard',
            'kk_profiling.update_required',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
