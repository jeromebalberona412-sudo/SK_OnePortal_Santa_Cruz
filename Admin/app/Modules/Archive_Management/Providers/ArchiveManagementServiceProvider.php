<?php

namespace App\Modules\Archive_Management\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ArchiveManagementServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'archive-management');

        Route::middleware(['web', 'auth', 'ensure.password.setup', 'role:admin'])
            ->group(__DIR__ . '/../routes/web.php');
    }
}
