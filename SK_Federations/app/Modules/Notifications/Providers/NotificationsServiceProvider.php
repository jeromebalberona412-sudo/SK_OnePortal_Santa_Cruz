<?php

namespace App\Modules\Notifications\Providers;

use App\Modules\Notifications\Services\SampleNotificationService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NotificationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SampleNotificationService::class);
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViewsFrom(__DIR__.'/../Views', 'notifications');

        $this->publishes([
            __DIR__.'/../assets' => public_path('modules/notifications'),
        ], 'notifications-assets');

        View::composer('layout::header', function ($view): void {
            $service = app(SampleNotificationService::class);

            $view->with([
                'notifications' => $service->dropdownSamples(),
                'unreadNotificationCount' => $service->unreadCount(),
            ]);
        });
    }

    protected function loadRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/notifications.php');
    }
}
