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

    /** @var callable */
    protected $isCompleted;

    /** @var callable|null */
    protected $isSkipped = null;

    protected array $whitelisted = [];

    protected array $whitelistedRoutes = [];

    public function __construct(
        public string $name
    ) {
    }

    public function user(Authenticatable $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function whitelist(array $path = []): self
    {
        $this->whitelisted = [...$this->whitelisted, ...$path];

        return $this;
    }

    public function whitelistRoutes(array $routes = []): self
    {
        $this->whitelistedRoutes = [...$this->whitelistedRoutes, ...$routes];

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

        if ($this->isCompleted === null || $this->url() === null) {
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

    public function url(): ?string
    {
        if ($this->href === null) {
            return null;
        }

        return ($this->href)();
    }

    public function href(string $href): self
    {
        $this->href = fn () => $href;

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

    public function skipIf(callable $isRequired): self
    {
        $this->isSkipped = $isRequired;

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

        foreach ($this->whitelisted as $path) {
            if ((new UrlPatternMatcher($path))->match($request->path())) {
                return true;
            }
        }

        foreach ($this->whitelistedRoutes as $route) {
            if ($request->routeIs($route)) {
                return true;
            }
        }

        return false;
    }
}
