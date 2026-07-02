<?php

namespace App\Modules\Layout\Providers;

use App\Services\ArchiveTermService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class LayoutServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom($this->resolveViewsPath(), 'layout');

        View::composer('layout::partials.archive-show-filter', function ($view) {
            $service = app(ArchiveTermService::class);
            $terms = $service->termsForUser(Auth::user());

            $view->with([
                'archiveTerms' => $terms,
                'activeArchiveTermId' => $service->activeTermId($terms),
            ]);
        });
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
