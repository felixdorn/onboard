<?php

namespace Felix\Onboard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Onboard implements Arrayable, Jsonable
{
    public function __construct(
        public Authenticatable $user,
        /** @var array<int, Step> */
        public array $steps = [],
    ) {
    }

    public function progress(): float
    {
        $step = $this->nextUnfinishedStep();

        if ($step === null) {
            return 100;
        }

        return (array_search($step, $this->steps, true)) / count($this->steps) * 100;
    }

    public function nextUnfinishedStep(): ?Step
    {
        // TODO: When L9 is released, PHPStan will understand that.
        /* @phpstan-ignore-next-line */
        return collect($this->steps)->first(fn (Step $step) => $step->user($this->user)->isIncomplete());
    }

    public function inProgress(): bool
    {
        return !$this->isFinished();
    }

    public function isFinished(): bool
    {
        return $this->nextUnfinishedStep() === null;
    }

    public function toJson($options = 0): false|string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toArray(): array
    {
        $currentStep = $this->nextUnfinishedStep();

        if ($currentStep === null) {
            $finished     = count($this->steps);
            $currentIndex = $finished - 1;
        } else {
            $currentIndex = array_search($currentStep, $this->steps, true);
            $finished     = $currentIndex + 1;
        }

        return [
            'finished'      => $finished,
            'total'         => count($this->steps),
            'current'       => $currentStep?->toArray(),
            'current_index' => $currentIndex,
            'steps'         => array_map(fn (Step $step)         => $step->user($this->user)->toArray(), $this->steps),
        ];
    }
}
