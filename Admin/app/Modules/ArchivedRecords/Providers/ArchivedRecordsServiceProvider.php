<?php

namespace App\Modules\ArchivedRecords\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ArchivedRecordsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'archived-records');

        Route::middleware(['web', 'auth', 'ensure2fa', 'role:admin'])
            ->group(__DIR__ . '/../routes/web.php');
    }
}
