<?php

namespace App\Modules\Manage_Location\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ManageLocationServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'manage_location');
        $this->loadMigrationsFrom(database_path('migrations'));

        Route::middleware('web')
            ->group(__DIR__ . '/../routes/web.php');
    }
}
