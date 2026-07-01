<?php

namespace App\Providers;

use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Accounts\Policies\AccountPolicy;
use App\Modules\Shared\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(User::class, AccountPolicy::class);
        Gate::policy(OfficialProfile::class, AccountPolicy::class);
        Gate::define('manage-accounts', fn (User $user) => $user->isFederationAdministrator());

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
            'community_feed::*',
        ], function ($view): void {
            $user = Auth::user();

            $user?->loadMissing('officialProfile');

            $view->with([
                'user' => $user,
                'avatar' => asset('Images/SK_Fed_profile.png'),
                'formattedRole' => match (true) {
                    $user?->isSkFed() => 'SK Federation',
                    $user?->hasFederationLeadershipAccess() => (string) ($user->officialProfile?->federation_position ?? 'SK Federation'),
                    (string) ($user?->role ?? '') === 'admin' => 'Administrator',
                    default => $user?->role
                        ? ucwords(str_replace('_', ' ', (string) $user->role))
                        : 'SK Federation',
                },
            ]);
        });
    }
}
