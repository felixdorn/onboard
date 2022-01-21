<?php

namespace Felix\Onboard;

use Felix\Onboard\Exceptions\StepCanNeverBeCompletedException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Step implements Arrayable, Jsonable
{
    public ?string $href = null;

    public array $context = [];

    /** @var callable */
    private $isCompleted;

    /** @var callable|null */
    private $isSkipped = null;

    public function __construct(
        public Authenticatable $user,
        public string $name
    ) {
    }

    public function href(string $href): self
    {
        $this->href = $href;

        return $this;
    }

    public function route(callable|string $route, array $attributes = []): self
    {
        if (is_callable($route)) {
            $this->href = $route();

            return $this;
        }

        $this->href = route($route, $attributes);

        return $this;
    }

    public function completedIf(callable $isCompleted): self
    {
        $this->isCompleted = $isCompleted;

        return $this;
    }

    public function context(callable|array $attributes = []): self
    {
        /** @var array<string, mixed> $attributes */
        $attributes = value($attributes);

        foreach ($attributes as $key => $value) {
            $attributes[$key] = value($value);
        }

        $this->context = $attributes;

        return $this;
    }

    public function isIncomplete(): bool
    {
        return !$this->isComplete();
    }

    public function isComplete(): bool
    {
        if ($this->isSkipped()) {
            return true;
        }

        if ($this->isCompleted === null || $this->href === null) {
            throw new StepCanNeverBeCompletedException(sprintf('Step [%s] can never be completed', $this->name));
        }

        /* @phpstan-ignore-next-line */
        return app()->call($this->isCompleted, ['user' => $this->user]);
    }

    public function isSkipped(): bool
    {
        if ($this->isSkipped === null) {
            return false;
        }

        /* @phpstan-ignore-next-line */
        return app()->call($this->isSkipped, ['user' => $this->user]);
    }

    public function __get(string $name): mixed
    {
        return $this->context[$name] ?? null;
    }

    public function toJson($options = 0): false|string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toArray(): array
    {
        return [
            'name'      => $this->name,
            'href'      => $this->href,
            'completed' => $this->isComplete(),
            'context'   => $this->context,
        ];
    }

    public function skipIf(callable $isRequired): self
    {
        $this->isSkipped = $isRequired;

        return $this;
    }
}
