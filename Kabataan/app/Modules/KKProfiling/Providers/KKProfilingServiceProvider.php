<?php

namespace App\Modules\KKProfiling\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class KKProfilingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'kkprofiling');

        Route::middleware('web')
            ->group(__DIR__ . '/../Routes/web.php');

        view()->composer('kkprofiling::*', function ($view) {
            $view->with('fvCameraConfig', [
                'devHttpsPort'    => (int) config('kkprofiling.camera.dev_https_port', 8443),
                'devHttpsEnabled' => (bool) config('kkprofiling.camera.dev_https_enabled', false),
                'isSecureRequest' => request()->secure(),
                'appEnv'          => config('app.env'),
            ]);
        });
    }
}
