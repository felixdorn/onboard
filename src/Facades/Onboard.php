<?php

namespace Felix\Onboard\Facades;

use Felix\Onboard\Step;
use Felix\Onboard\StepsCache;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Step add(string $name)
 * @method static static allow(string ...$paths)
 * @method static static allowRoutes(string ...$routes)
 */
class Onboard extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return StepsCache::class;
    }
}
