<?php

return [
    App\Providers\AppServiceProvider::class,
    Laravel\Fortify\FortifyServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Modules\Authentication\Providers\AuthenticationServiceProvider::class,
    App\Modules\Profile\Providers\ProfileServiceProvider::class,
    App\Modules\Layout\Providers\LayoutServiceProvider::class,
    App\Modules\Reports_Management\ReportsManagementServiceProvider::class,
];
