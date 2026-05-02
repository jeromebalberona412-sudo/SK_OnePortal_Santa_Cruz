<?php

return [
    App\Providers\AppServiceProvider::class,
    Laravel\Fortify\FortifyServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Modules\Shared\Providers\SharedServiceProvider::class,
    App\Modules\Authentication\Providers\AuthenticationServiceProvider::class,
    App\Modules\Dashboard\Providers\DashboardServiceProvider::class,
    App\Modules\Profile\Providers\ProfileServiceProvider::class,
    App\Modules\CommunityFeed\Providers\CommunityFeedServiceProvider::class,
    App\Modules\BarangayMonitoring\Providers\BarangayMonitoringServiceProvider::class,
    App\Modules\Reports\Providers\ReportsServiceProvider::class,
    App\Modules\KabataanMonitoring\Providers\KabataanMonitoringServiceProvider::class,
    App\Modules\Archive\Providers\ArchiveServiceProvider::class,
];
