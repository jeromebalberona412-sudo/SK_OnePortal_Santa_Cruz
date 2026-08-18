<?php

namespace App\Modules\Authentication\Providers;

use App\Modules\Authentication\Services\AccountActivationRecoveryService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AccountActivationRecoveryService::class);
    }

    public function boot(): void
    {
        $this->configureAccountActivationRateLimiters();
        $this->loadRoutes();
        $this->loadViewsFrom(__DIR__.'/../Views', 'authentication');
    }

    protected function loadRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/auth.php');
    }

    protected function configureAccountActivationRateLimiters(): void
    {
        $ipLimitPerMinute = max(1, (int) config('kabataan_auth.account_activation.rate_limit.ip_per_minute', 5));
        $emailLimitPerHour = max(1, (int) config('kabataan_auth.account_activation.rate_limit.email_per_hour', 3));

        RateLimiter::for('kabataan-account-activation-ip', function (Request $request) use ($ipLimitPerMinute) {
            return Limit::perMinute($ipLimitPerMinute)
                ->by('kabataan-account-activation-ip:'.$request->ip())
                ->response(fn (Request $request, array $headers) => $this->activationRateLimitedResponse($request, $headers));
        });

        RateLimiter::for('kabataan-account-activation-email', function (Request $request) use ($emailLimitPerHour) {
            $normalizedEmail = Str::lower(trim((string) $request->input('email', '')));
            $emailHash = $normalizedEmail === '' ? 'missing' : hash('sha256', $normalizedEmail);

            return Limit::perHour($emailLimitPerHour)
                ->by('kabataan-account-activation-email:'.$emailHash)
                ->response(fn (Request $request, array $headers) => $this->activationRateLimitedResponse($request, $headers));
        });
    }

    protected function activationRateLimitedResponse(Request $request, array $headers)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => AccountActivationRecoveryService::THROTTLED_MESSAGE,
            ], 429, $headers);
        }

        return redirect()->back()
            ->withInput()
            ->with('verify_account_error', AccountActivationRecoveryService::THROTTLED_MESSAGE)
            ->setStatusCode(429)
            ->withHeaders($headers);
    }
}
