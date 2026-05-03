<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        \Illuminate\Support\Facades\Mail::extend('brevo', function () {
            return new \Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport(
                (string) config('services.brevo.key', env('BREVO_KEY', ''))
            );
        });

        $this->loadModuleRoutes();
        $this->loadModuleViews();
        $this->shareBarangayLogo();
    }

    private function shareBarangayLogo(): void
    {
        View::composer('layout::sidebar', function ($view) {
            $user = Auth::user();

            $barangayName = null;
            $barangayLogoUrl = null;

            if ($user && $user->barangay_id) {
                $barangayName = DB::table('barangays')
                    ->where('id', $user->barangay_id)
                    ->value('name');

                $barangayLogoUrl = DB::table('barangay_logos')
                    ->where('barangay_id', $user->barangay_id)
                    ->value('url');
            }

            $view->with([
                'barangayName'    => $barangayName,
                'barangayLogoUrl' => $barangayLogoUrl,
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
                
                if (is_dir($viewsPath)) {
                    $this->loadViewsFrom($viewsPath, $module);
                }
            }
        }
    }
}
