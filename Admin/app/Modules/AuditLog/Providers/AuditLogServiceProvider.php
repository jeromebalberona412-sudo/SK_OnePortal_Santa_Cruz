<?php

namespace App\Modules\AuditLog\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\AuditLog\Contracts\AuditLogInterface;
use App\Modules\AuditLog\Services\AuditLogService;
use Illuminate\Support\Facades\Route;

class AuditLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogInterface::class, AuditLogService::class);
        $this->app->singleton(AuditLogService::class);
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViewsFrom(__DIR__ . '/../views', 'auditlogs');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    protected function loadRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../routes/web.php');
    }
}
