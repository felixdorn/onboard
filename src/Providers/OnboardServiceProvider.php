<?php

namespace Felix\Onboard\Providers;

use Felix\Onboard\StepsCache;
use Illuminate\Support\ServiceProvider;

class OnboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StepsCache::class);
    }
}
