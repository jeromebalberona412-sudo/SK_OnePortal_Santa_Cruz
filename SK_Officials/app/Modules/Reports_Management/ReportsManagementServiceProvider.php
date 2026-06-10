<?php

namespace App\Modules\Reports_Management;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ReportsManagementServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/Views', 'Reports_Management');

        Route::middleware('web')
            ->group(__DIR__.'/Routes/reports-management.php');
    }
}
