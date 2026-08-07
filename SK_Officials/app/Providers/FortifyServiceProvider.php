<?php

namespace App\Providers;

use App\Models\User;
use App\Modules\Authentication\Services\AuthenticationService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RedirectsIfTwoFactorAuthenticatable;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register custom login response handler
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse
            {
                public function toResponse($request)
                {
                    // For JSON requests (API)
                    if ($request->expectsJson()) {
                        return response()->json([
                            'two_factor' => false,
                            'redirect' => route('dashboard'),
                        ], 200);
                    }

                    // For web requests - redirect to dashboard
                    return redirect()->intended(route('dashboard'));
                }
            };
        });
    }

    public function boot(): void
    {
        Fortify::loginView(fn () => view('authentication::login'));
        Fortify::verifyEmailView(fn () => view('authentication::verify-notice'));
        Fortify::requestPasswordResetLinkView(fn () => view('authentication::forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('authentication::reset-password', ['request' => $request]));

        Fortify::authenticateThrough(function (Request $request) {
            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
                Features::enabled(Features::twoFactorAuthentication()) ? RedirectsIfTwoFactorAuthenticatable::class : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });

        Fortify::authenticateUsing(function (Request $request) {
            $user = app(AuthenticationService::class)->authenticate($request);

            if ($user === null) {
                if ($request->session()->has('sk_official_email_verification_pending')) {
                    throw new HttpResponseException(
                        redirect()->route('sk_official.verification.wait')
                    );
                }

                throw ValidationException::withMessages([
                    'email' => ['Invalid Email or Password'],
                    'password' => ['Invalid Email or Password'],
                ]);
            }

            return $user;
        });

        // After successful login, claim the session
        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User && request()->hasSession()) {
                app(AuthenticationService::class)->claimCurrentSession($event->user, request());
            }
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user instanceof User && request()->hasSession()) {
                app(AuthenticationService::class)->clearSessionOwnershipOnLogout($event->user, request());
            }
        });
    }
}
