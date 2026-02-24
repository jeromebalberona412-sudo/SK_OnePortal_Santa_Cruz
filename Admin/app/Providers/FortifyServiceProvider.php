<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Support\ServiceProvider;
use App\Modules\Authentication\Services\AuthenticationService;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Fortify::loginView(function () {
            return view('authentication::login');
        });

        Fortify::twoFactorChallengeView(function () {
            return view('authentication::two-factor-challenge');
        });

        Fortify::authenticateUsing(function (Request $request) {
            $authService = app(AuthenticationService::class);
            return $authService->authenticate($request);
        });

        Fortify::confirmPasswordView(function () {
            return view('authentication::confirm-password');
        });
    }
}
