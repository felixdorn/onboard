<?php

namespace Felix\Onboard\Concerns;

use Felix\Onboard\Onboard;
use Felix\Onboard\StepsCache;
use Illuminate\Auth\Authenticatable;

/**
 * @mixin Authenticatable
 */
trait GetsOnboarded
{
    public function onboarding(): Onboard
    {
        $cache = app(StepsCache::class);

        return new Onboard($this, $cache->steps);
    }
}
