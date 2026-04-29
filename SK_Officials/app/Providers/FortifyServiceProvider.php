<?php

namespace App\Providers;

use App\Models\User;
use App\Modules\Authentication\Services\AuthenticationService;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Fortify::loginView(fn () => view('Authentication::login'));
        Fortify::verifyEmailView(fn () => view('authentication::verify-notice'));

        Fortify::authenticateUsing(function (Request $request) {
            $user = app(AuthenticationService::class)->authenticate($request);

            if ($user === null) {
                throw ValidationException::withMessages([
                    'email' => ['Invalid Email or Password'],
                    'password' => ['Invalid Email or Password'],
                ]);
            }

            return $user;
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user instanceof User && request()->hasSession()) {
                app(AuthenticationService::class)->clearSessionOwnershipOnLogout($event->user, request());
            }
        });
    }
}
