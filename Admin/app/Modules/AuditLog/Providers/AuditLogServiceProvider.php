<?php

namespace App\Modules\AuditLog\Providers;

use App\Modules\AuditLog\Contracts\AuditLogInterface;
use App\Modules\AuditLog\Services\AuditLogService;
use Illuminate\Support\ServiceProvider;

class AuditLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogInterface::class, AuditLogService::class);
        $this->app->singleton(AuditLogService::class);
    }

    public function boot(): void
    {
        //
    }
}
