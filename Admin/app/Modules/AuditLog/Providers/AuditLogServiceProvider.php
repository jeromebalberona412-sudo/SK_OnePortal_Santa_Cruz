<?php

namespace App\Modules\AuditLog\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\AuditLog\Contracts\AuditLogInterface;
use App\Modules\AuditLog\Services\AuditLogService;

class AuditLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogInterface::class, AuditLogService::class);
        $this->app->singleton(AuditLogService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
