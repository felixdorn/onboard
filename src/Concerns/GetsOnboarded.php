<?php

namespace Felix\Onboard\Concerns;

use Felix\Onboard\Onboard;

trait GetsOnboarded
{
    public function onboarding(): Onboard
    {
        return app(Onboard::class, ['user' => $this]);
    }
}
