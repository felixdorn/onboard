<?php

namespace Felix\Onboard;

/**
 * @internal
 */
class StepsCache
{
    public array $steps = [];

    public array $allowed = [];

    public array $allowedRoutes = [];

    public function add(string $name): Step
    {
        $this->steps[] = $step = (new Step($name))->allow($this->allowed)->allowRoutes($this->allowedRoutes);

        return $step;
    }

    public function allow(array $paths = []): self
    {
        $this->allowed = [...$this->allowed, ...$paths];

        return $this;
    }

    public function allowRoutes(array $routes = []): self
    {
        $this->allowedRoutes = [...$this->allowedRoutes, ...$routes];

        return $this;
    }
}
