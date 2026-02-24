<?php

namespace App\Modules\Authentication\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Services\LoginSecurityService;
use App\Modules\Authentication\Services\AuthenticationService;

class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoginSecurityService::class);
        $this->app->singleton(AuthenticationService::class);
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViewsFrom(__DIR__ . '/../Views', 'authentication');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    protected function loadRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../Routes/auth.php');
    }
}
