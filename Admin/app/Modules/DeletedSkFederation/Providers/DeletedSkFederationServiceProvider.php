<?php

namespace App\Modules\DeletedSkFederation\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class DeletedSkFederationServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'deleted-sk-federation');

        Route::middleware(['web', 'auth', 'ensure2fa', 'role:admin'])
            ->group(__DIR__ . '/../routes/web.php');
    }
}
