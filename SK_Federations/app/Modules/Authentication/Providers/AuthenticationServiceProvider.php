<?php

namespace App\Modules\Authentication\Providers;

use App\Modules\Authentication\Services\AuthAuditLogService;
use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Authentication\Services\DeviceFingerprintService;
use App\Modules\Authentication\Services\EmailVerificationDeviceService;
use App\Modules\Authentication\Services\FeatureFlagService;
use App\Modules\Authentication\Services\LoginSecurityService;
use App\Modules\Authentication\Services\SuspiciousLoginService;
use App\Modules\Authentication\Services\TenantContextService;
use App\Modules\Authentication\Services\TrustedDeviceService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContextService::class);
        $this->app->singleton(FeatureFlagService::class);
        $this->app->singleton(AuthAuditLogService::class);
        $this->app->singleton(DeviceFingerprintService::class);
        $this->app->singleton(EmailVerificationDeviceService::class);
        $this->app->singleton(TrustedDeviceService::class);
        $this->app->singleton(LoginSecurityService::class);
        $this->app->singleton(SuspiciousLoginService::class);
        $this->app->singleton(AuthenticationService::class);
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViewsFrom(__DIR__.'/../Views', 'authentication');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->publishes([
            __DIR__.'/../assets' => public_path('modules/authentication'),
        ], 'auth-assets');
    }

    protected function loadRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/auth.php');
    }
}
