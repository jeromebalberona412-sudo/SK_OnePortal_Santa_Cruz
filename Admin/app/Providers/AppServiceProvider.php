<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Services are now registered in their respective module providers
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure rate limiting for login attempts
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email', '');
            $ip = $request->ip();

            return [
                // 5 attempts per minute per IP
                Limit::perMinute(5)->by($ip)->response(function () {
                    return response()->json([
                        'message' => 'Too many login attempts. Please try again later.'
                    ], 429);
                }),
                
                // 5 attempts per 5 minutes per email
                Limit::perMinutes(5, 5)->by($email)->response(function () {
                    return response()->json([
                        'message' => 'Too many login attempts. Please try again later.'
                    ], 429);
                }),
            ];
        });
    }
}
