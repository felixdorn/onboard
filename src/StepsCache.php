<?php

namespace Felix\Onboard;

/**
 * @internal
 */
class StepsCache
{
    public array $steps = [];

    public function add(string $name): Step
    {
        $this->steps[] = $step = new Step($name);

        return $step;
    }
}
