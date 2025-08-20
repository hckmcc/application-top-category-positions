<?php

namespace App\Providers;

use App\Services\AppticaApiService;
use App\Services\CategoryPositionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AppticaApiService::class);

        $this->app->singleton(CategoryPositionService::class, function ($app) {
            return new CategoryPositionService(
                $app->make(AppticaApiService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
