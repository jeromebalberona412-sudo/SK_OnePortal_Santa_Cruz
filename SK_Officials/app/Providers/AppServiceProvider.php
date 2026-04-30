<?php

namespace App\Providers;

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
        // Load module routes
        $this->loadModuleRoutes();
        
        // Load module views
        $this->loadModuleViews();
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
