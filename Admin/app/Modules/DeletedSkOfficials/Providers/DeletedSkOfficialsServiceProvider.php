<?php

namespace App\Modules\DeletedSkOfficials\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class DeletedSkOfficialsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'deleted-sk-officials');

        Route::middleware(['web', 'auth', 'ensure2fa', 'role:admin'])
            ->group(__DIR__ . '/../routes/web.php');
    }
}
