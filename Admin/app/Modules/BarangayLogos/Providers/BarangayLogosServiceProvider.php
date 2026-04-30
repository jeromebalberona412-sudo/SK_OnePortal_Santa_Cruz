<?php

namespace App\Modules\BarangayLogos\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BarangayLogosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Views', 'barangay_logos');

        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');
    }
}
