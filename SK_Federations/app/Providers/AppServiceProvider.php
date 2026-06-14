<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
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
        $applicationUrl = (string) config('app.url');

        if ($applicationUrl !== '') {
            URL::forceRootUrl($applicationUrl);

            if (str_starts_with($applicationUrl, 'https://')) {
                URL::forceScheme('https');
            } elseif (str_starts_with($applicationUrl, 'http://')) {
                URL::forceScheme('http');
            }
        }

        View::composer([
            'layout::header',
            'layout::sidebar',
        ], function ($view): void {
            $user = Auth::user();

            $view->with([
                'user' => $user,
                'avatar' => asset('Images/SK_Fed_profile.png'),
                'formattedRole' => match ((string) ($user?->role ?? '')) {
                    'sk_fed' => 'SK Federation',
                    'admin' => 'Administrator',
                    default => $user?->role
                        ? ucwords(str_replace('_', ' ', (string) $user->role))
                        : 'SK Federation',
                },
            ]);
        });
    }
}
