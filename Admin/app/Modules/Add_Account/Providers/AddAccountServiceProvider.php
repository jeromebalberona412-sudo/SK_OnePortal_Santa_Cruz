<?php

namespace App\Modules\Add_Account\Providers;

use Illuminate\Support\ServiceProvider;

class AddAccountServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'add_account');
    }
}
