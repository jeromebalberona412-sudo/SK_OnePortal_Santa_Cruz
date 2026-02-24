<?php

return [
    App\Providers\AppServiceProvider::class,
    Laravel\Fortify\FortifyServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Modules\Shared\Providers\SharedServiceProvider::class,
    App\Modules\Authentication\Providers\AuthenticationServiceProvider::class,
    App\Modules\AuditLog\Providers\AuditLogServiceProvider::class,
    App\Modules\Dashboard\Providers\DashboardServiceProvider::class,
];
