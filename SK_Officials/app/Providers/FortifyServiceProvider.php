<?php

namespace App\Providers;

use App\Models\User;
use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Authentication\Services\TurnstileService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse;
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

        Fortify::authenticateUsing(function (Request $request) {
            // Verify Turnstile token if enabled
            if (config('services.turnstile.enabled')) {
                $token = (string) $request->input('cf-turnstile-response', '');
                $turnstileService = app(TurnstileService::class);

                if ($token === '') {
                    throw ValidationException::withMessages([
                        'captcha' => ['Please complete the security verification.'],
                    ]);
                }

                if (! $turnstileService->verify($token, $request->ip())) {
                    throw ValidationException::withMessages([
                        'captcha' => ['Security verification failed. Please try again.'],
                    ]);
                }
            }

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
