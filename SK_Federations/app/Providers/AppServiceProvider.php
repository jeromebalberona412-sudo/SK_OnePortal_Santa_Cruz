<?php

namespace App\Providers;

use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Accounts\Policies\AccountPolicy;
use App\Modules\Shared\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
        // Set custom temporary directory to avoid tempnam() errors
        $tempDir = storage_path('temp');
        if (! File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
        if (is_writable($tempDir)) {
            putenv('TMPDIR='.$tempDir);
            putenv('TEMP='.$tempDir);
            putenv('TMP='.$tempDir);
        }

        Gate::policy(User::class, AccountPolicy::class);
        Gate::policy(OfficialProfile::class, AccountPolicy::class);
        Gate::define('manage-accounts', fn (User $user) => $user->isFederationAdministrator());

        $applicationUrl = trim((string) config('app.url'));

        if ($applicationUrl !== '' && filter_var($applicationUrl, FILTER_VALIDATE_URL)) {
            URL::forceRootUrl($applicationUrl);

            if (str_starts_with(strtolower($applicationUrl), 'https://')) {
                URL::forceScheme('https');
            } elseif (str_starts_with(strtolower($applicationUrl), 'http://')) {
                URL::forceScheme('http');
            }
        }

        if (
            isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
            && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'
        ) {
            URL::forceScheme('https');
        }

        View::composer([
            'layout::header',
            'layout::sidebar',
            'community_feed::*',
        ], function ($view): void {
            /** @var User|null $user */
            $user = Auth::user();

            $user?->loadMissing('officialProfile');

            $view->with([
                'user' => $user,
                'avatar' => asset('Images/SK_Fed_profile.png'),
                'displayName' => $this->resolveSidebarDisplayName($user),
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

    private function resolveSidebarDisplayName(?User $user): string
    {
        if (! $user) {
            return 'User';
        }

        $profile = $user->officialProfile;
        if ($profile) {
            $parts = array_filter([
                trim((string) $profile->first_name),
                trim((string) $profile->middle_name),
                trim((string) $profile->last_name),
                trim((string) $profile->suffix),
            ], fn ($part) => $part !== '');

            if ($parts !== []) {
                return implode(' ', $parts);
            }
        }

        $name = trim((string) $user->name);

        return $name !== '' ? $name : 'User';
    }
}
