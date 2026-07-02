<?php

namespace App\Modules\Layout\Providers;

use Illuminate\Support\ServiceProvider;

class LayoutServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom($this->resolveViewsPath(), 'layout');
    }

    private function resolveViewsPath(): string
    {
        foreach (['views', 'Views'] as $directory) {
            $path = __DIR__."/../{$directory}";

            if (is_dir($path)) {
                return $path;
            }
        }

        return __DIR__.'/../views';
    }
}
