<?php

namespace App\Providers;

use App\Contracts\PlateProviderInterface;
use App\Services\ApiPlaqueProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PlateProviderInterface::class, ApiPlaqueProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
