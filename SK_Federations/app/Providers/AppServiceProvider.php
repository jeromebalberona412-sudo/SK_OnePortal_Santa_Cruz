<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $applicationUrl = (string) config('app.url');

        if ($applicationUrl === '') {
            return;
        }

        URL::forceRootUrl($applicationUrl);

        if (str_starts_with($applicationUrl, 'https://')) {
            URL::forceScheme('https');
        } elseif (str_starts_with($applicationUrl, 'http://')) {
            URL::forceScheme('http');
        }
    }
}
