<?php

namespace Felix\Onboard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Onboard implements Arrayable, Jsonable
{
    /** @var array<int, Step> */
    protected array $steps = [];

    public function __construct(
        public Authenticatable $user
    ) {
    }

    public function add(string $name): Step
    {
        $this->steps[] = $step = new Step($this->user, $name);

        return $step;
    }

    public function steps(): array
    {
        return $this->steps;
    }

    public function inProgress(): bool
    {
        return !$this->isFinished();
    }

    public function isFinished(): bool
    {
        return $this->nextUnfinishedStep() === null;
    }

    public function nextUnfinishedStep(): ?Step
    {
        // TODO: When L9 is released, PHPStan will understand that.
        /* @phpstan-ignore-next-line */
        return collect($this->steps)
            ->first(fn (Step $step) => $step->isIncomplete());
    }

    public function toJson($options = 0): false|string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toArray(): array
    {
        return array_map(fn (Step $step) => $step->toArray(), $this->steps);
    }
}
