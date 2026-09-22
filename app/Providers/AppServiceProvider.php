<?php

namespace App\Providers;

use App\Services\FirebaseAuthService;
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
        //
    }
}
