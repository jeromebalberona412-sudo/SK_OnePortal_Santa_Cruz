<?php

namespace App\Providers;

use App\Services\KabataanNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        View::composer(['layout::kabataan-header', 'dashboard::notification'], function ($view) {
            $user = Auth::user();
            $notificationService = app(KabataanNotificationService::class);

            $view->with([
                'headerNotifications' => $notificationService->recentForUser($user, 8),
                'unreadNotificationCount' => $notificationService->unreadCountForUser($user),
            ]);
        });
    }
}
