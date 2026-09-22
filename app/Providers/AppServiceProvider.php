<?php

namespace App\Providers;

use App\Services\FirebaseAuthService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FirebaseAuthService::class, function () {
            return new FirebaseAuthService(
                projectId: config('services.firebase.project_id')
            );
        });
        $this->app->singleton(\App\Services\RecommendationEngine::class);
        $this->app->singleton(\App\Services\RecommendationEngine::class);
$this->app->singleton(\App\Services\RoutineBuilder::class);
    }

    public function boot(): void
    {
        // This app is Bootstrap 5, not Tailwind. Laravel defaults to the
        // Tailwind paginator, which renders unstyled (and with oversized inline
        // SVG arrows) here. Use the Bootstrap 5 paginator views instead.
        Paginator::useBootstrapFive();
    }
}
