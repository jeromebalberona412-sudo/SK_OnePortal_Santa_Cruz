<?php

namespace App\Modules\Manage_Kabataan\Providers;

use App\Modules\Manage_Kabataan\Services\ManageKabataanService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ManageKabataanServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ManageKabataanService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'manage-kabataan');

        Route::middleware('web')
            ->group(__DIR__ . '/../routes/web.php');
    }
}
