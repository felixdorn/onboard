<?php

namespace Felix\Onboard;

use Felix\Onboard\Console\CreateMiddlewareCommand;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StepsCache::class);
    }

    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            CreateMiddlewareCommand::class,
        ]);
    }
}
