<?php

namespace App\Modules\ContactUs\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ContactUsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Views', 'contact-us');

        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');
    }
}
