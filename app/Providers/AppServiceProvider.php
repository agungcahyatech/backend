<?php

namespace App\Providers;

use App\Models\GameConfiguration;
use App\Models\GameConfigurationField;
use App\Observers\GameConfigurationObserver;
use App\Observers\GameConfigurationFieldObserver;
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
        // Register observers for auto-cache clearing
        GameConfiguration::observe(GameConfigurationObserver::class);
        GameConfigurationField::observe(GameConfigurationFieldObserver::class);
    }
}
