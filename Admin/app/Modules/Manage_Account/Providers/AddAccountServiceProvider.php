<?php

namespace App\Modules\Manage_Account\Providers;

use Illuminate\Support\ServiceProvider;

class AddAccountServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'manage_account');
    }
}
