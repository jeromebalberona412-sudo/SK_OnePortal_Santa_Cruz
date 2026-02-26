<?php

return [
    App\Modules\Add_Account\Providers\AddAccountServiceProvider::class,
    App\Modules\AuditLog\Providers\AuditLogServiceProvider::class,
    App\Modules\Authentication\Providers\AuthenticationServiceProvider::class,
    App\Modules\Dashboard\Providers\DashboardServiceProvider::class,
    App\Modules\Shared\Providers\SharedServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    Laravel\Fortify\FortifyServiceProvider::class,
];
