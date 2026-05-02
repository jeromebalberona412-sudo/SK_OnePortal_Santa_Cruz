<?php

namespace App\Modules\Archive\Providers;

use Illuminate\Support\ServiceProvider;

class ArchiveServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Views', 'archive');
        $this->publishes([
            __DIR__ . '/../assets' => public_path('modules/archive'),
        ], 'archive-assets');
    }
}
