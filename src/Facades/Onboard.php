<?php

namespace Felix\Onboard\Facades;

use Felix\Onboard\Step;
use Felix\Onboard\StepsCache;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Step add(string $name)
 */
class Onboard extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return StepsCache::class;
    }
}
