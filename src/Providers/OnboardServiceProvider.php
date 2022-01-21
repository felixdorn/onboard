<?php

namespace Felix\Onboard\Providers;

use Felix\Onboard\Onboard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\ServiceProvider;

class OnboardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Onboard::class, fn (Authenticatable $user) => new Onboard($user));
    }
}
