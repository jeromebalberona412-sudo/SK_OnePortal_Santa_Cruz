<?php

return [

App\Providers\AppServiceProvider::class,
Laravel\Fortify\FortifyServiceProvider::class,
App\Providers\FortifyServiceProvider::class,
App\Modules\Shared\Providers\SharedServiceProvider::class,
App\Modules\Layout\Providers\LayoutServiceProvider::class,
App\Modules\Authentication\Providers\AuthenticationServiceProvider::class,
App\Modules\AuditLog\Providers\AuditLogServiceProvider::class,
App\Modules\Dashboard\Providers\DashboardServiceProvider::class,
App\Modules\Profile\Providers\ProfileServiceProvider::class,
App\Modules\Accounts\Providers\AccountsServiceProvider::class,
App\Modules\BarangayLogos\Providers\BarangayLogosServiceProvider::class,
App\Modules\DeletedSkFederation\Providers\DeletedSkFederationServiceProvider::class,
App\Modules\DeletedSkOfficials\Providers\DeletedSkOfficialsServiceProvider::class,
App\Modules\ArchivedRecords\Providers\ArchivedRecordsServiceProvider::class,

];
