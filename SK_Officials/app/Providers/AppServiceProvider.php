<?php

namespace App\Providers;

use App\Services\BarangayLogoUrlService;
use App\Services\SkOfficialsNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // 🔒 Force HTTPS for Railway/Production SSL & fix missing asset styles
        if ($this->app->environment('production') || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\Mail::extend('brevo', function () {
            return new \Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport(
                (string) config('services.brevo.key', env('BREVO_KEY', ''))
            );
        });

        $this->loadModuleRoutes();
        $this->loadModuleViews();
        $this->shareLayoutUserContext();
        $this->configureRememberDuration();
    }

    private function configureRememberDuration(): void
    {
        $lifetimeDays = max(1, (int) config('sk_official_auth.remember.lifetime_days', 7));

        Auth::guard('web')->setRememberDuration($lifetimeDays * 24 * 60);
    }

    private function shareLayoutUserContext(): void
    {
        View::composer(['layout::sidebar', 'layout::header'], function ($view) {
            $user = Auth::user();

            $barangayName    = null;
            $barangayLogoUrl = null;
            $userDisplayName = 'SK Officials User';

            if ($user) {
                $userDisplayName = $user->name ?: 'SK Officials User';

                if ($user->barangay_id) {
                    // Cache barangay name + logo per barangay for 30 minutes.
                    // These values are static for the lifetime of a session and
                    // querying them on every sidebar/header render adds 2 DB
                    // round-trips per page load.
                    $cacheKey = "sk_official_barangay_ctx:{$user->barangay_id}";

                    $barangayCtx = Cache::remember($cacheKey, 1800, function () use ($user) {
                        $name = DB::table('barangays')
                            ->where('id', $user->barangay_id)
                            ->value('name');

                        $logoUrl = app(BarangayLogoUrlService::class)->resolve($user->barangay_id);

                        return ['name' => $name, 'logo' => $logoUrl];
                    });

                    $barangayName    = $barangayCtx['name'];
                    $barangayLogoUrl = $barangayCtx['logo'];
                }
            }

            $view->with([
                'barangayName'    => $barangayName,
                'barangayLogoUrl' => $barangayLogoUrl,
                'userDisplayName' => $userDisplayName,
                'userAvatarUrl'   => $barangayLogoUrl ?: asset('images/SK_OnePortal_logo.png'),
                'userAvatarAlt'   => ($barangayName ?? 'SK OnePortal') . ' Logo',
            ]);
        });

        View::composer(['layout::header'], function ($view) {
            $user = Auth::user();
            $notificationService = app(SkOfficialsNotificationService::class);

            // Cache unread count per user for 60 seconds — avoids a COUNT query
            // on every page load while still reflecting new notifications quickly.
            $unreadCount = Cache::remember(
                "sk_official_notif_unread:{$user?->id}",
                60,
                fn () => $notificationService->unreadCountForUser($user),
            );

            $view->with([
                'headerNotifications'    => $notificationService->allForUser($user),
                'unreadNotificationCount' => $unreadCount,
            ]);
        });
    }
    
    /**
     * Load routes from all modules
     */
    private function loadModuleRoutes(): void
    {
        $modulesPath = app_path('Modules');
        
        if (is_dir($modulesPath)) {
            $modules = scandir($modulesPath);
            
            foreach ($modules as $module) {
                if ($module === '.' || $module === '..') {
                    continue;
                }
                
                $routesPath = $modulesPath . '/' . $module . '/routes';
                
                if (is_dir($routesPath)) {
                    $routeFiles = glob($routesPath . '/*.php');
                    
                    foreach ($routeFiles as $routeFile) {
                        require $routeFile;
                    }
                }
            }
        }
    }
    
    /**
     * Load views from all modules
     */
    private function loadModuleViews(): void
    {
        $modulesPath = app_path('Modules');
        
        if (is_dir($modulesPath)) {
            $modules = scandir($modulesPath);
            
            foreach ($modules as $module) {
                if ($module === '.' || $module === '..') {
                    continue;
                }
                
                $viewsPath = $modulesPath . '/' . $module . '/views';

                if (! is_dir($viewsPath)) {
                    $viewsPath = $modulesPath . '/' . $module . '/Views';
                }

                if ($module === 'Layout') {
                    continue;
                }
                
                if (is_dir($viewsPath)) {
                    $this->loadViewsFrom($viewsPath, $module);
                }
            }
        }
    }
}
