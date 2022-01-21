<?php

namespace Felix\Onboard;

use Felix\Onboard\Exceptions\StepCanNeverBeCompletedException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\RedirectResponse;

class Step implements Arrayable, Jsonable
{
    public Authenticatable $user;

    public ?string $href = null;

    /** @var callable */
    private $isCompleted;

    /** @var callable|null */
    private $isSkipped = null;

    public function __construct(
        public string $name
    ) {
    }

    public function user(Authenticatable $user): static
    {
        $this->user = $user;

        return $this;
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

        return ($this->isCompleted)($this->user);
    }

    public function isSkipped(): bool
    {
        if ($this->isSkipped === null) {
            return false;
        }

        return ($this->isSkipped)($this->user);
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
            'skipped'   => $this->isSkipped(),
        ];
    }

    public function skipIf(callable $isRequired): self
    {
        $this->isSkipped = $isRequired;

        return $this;
    }

    public function redirect(): RedirectResponse
    {
        /* @phpstan-ignore-next-line */
        return redirect()->to($this->href);
    }
}
