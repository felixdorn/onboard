<?php

namespace Felix\Onboard;

use Felix\Onboard\Exceptions\StepCanNeverBeCompletedException;
use Honda\UrlPatternMatcher\UrlPatternMatcher;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class Step implements Arrayable, Jsonable
{
    public Authenticatable $user;

    /** @var callable */
    protected $href;

    protected array $isCompleted = [];

    protected array $isSkipped = [];

    protected array $allowed = [];

    protected array $allowedRoutes = [];

    public function __construct(
        public string $name
    ) {
    }

    public function forUser(Authenticatable $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function allow(array $path = []): self
    {
        $this->allowed = [...$this->allowed, ...$path];

        return $this;
    }

    public function allowRoutes(array $routes = []): self
    {
        $this->allowedRoutes = [...$this->allowedRoutes, ...$routes];

        return $this;
    }

    public function route(callable|string $route, array $attributes = []): self
    {
        if (!is_callable($route)) {
            $route = fn () => route($route, $attributes);
        }

        $this->href = $route;

        return $this;
    }

    public function completedIf(callable $condition): self
    {
        $this->isCompleted[] = $condition;

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

        if ($this->isCompleted === [] || $this->url() === null) {
            throw new StepCanNeverBeCompletedException(sprintf('Step [%s] can never be completed', $this->name));
        }

        foreach ($this->isCompleted as $isCompleted) {
            if (!$isCompleted($this->user)) {
                return false;
            }
        }

        return true;
    }

    public function isSkipped(): bool
    {
        if ($this->isSkipped === []) {
            return false;
        }

        foreach ($this->isSkipped as $isSkipped) {
            if ($isSkipped($this->user)) {
                return true;
            }
        }

        return false;
    }

    public function url(): ?string
    {
        if ($this->href === null) {
            return null;
        }

        return ($this->href)();
    }

    public function href(string $href): self
    {
        $this->href = fn () => url($href);

        return $this;
    }

    public function toJson($options = 0): false|string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toArray(): array
    {
        return [
            'name'      => $this->name,
            'href'      => ($this->href)(),
            'completed' => $this->isComplete(),
            'skipped'   => $this->isSkipped(),
        ];
    }

    public function skipIf(callable $condition): self
    {
        $this->isSkipped[] = $condition;

        return $this;
    }

    public function redirect(): RedirectResponse
    {
        /* @phpstan-ignore-next-line */
        return redirect()->to($this->url());
    }

    public function usesRoute(Request $request): bool
    {
        $path = parse_url($this->url() ?? '', PHP_URL_PATH);

        if ($path === '/' . $request->path()) {
            return true;
        }

        foreach ($this->allowed as $path) {
            if ((new UrlPatternMatcher($path))->match($request->path())) {
                return true;
            }
        }

        foreach ($this->allowedRoutes as $route) {
            if ($request->routeIs($route)) {
                return true;
            }
        }

        return false;
    }
}
