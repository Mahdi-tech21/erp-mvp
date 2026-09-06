<?php

namespace App\Providers;

use App\Listeners\AuditLogSubscriber;
use App\Services\Assistant\AssistantManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AssistantManager::class);
    }

    public function boot(): void
    {
        Event::subscribe(AuditLogSubscriber::class);
    }
}
