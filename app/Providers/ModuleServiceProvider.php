<?php

namespace App\Providers;

use App\Support\ModuleRegistry;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class, fn ($app) => new ModuleRegistry($app));

        // Register each active module's provider now, during the register phase.
        $this->app->make(ModuleRegistry::class)->boot();
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $registry = $this->app->make(ModuleRegistry::class);

            $view->with('menu', $registry->menu());
            $view->with('company', $registry->company());
        });
    }
}
