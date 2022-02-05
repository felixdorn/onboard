<?php

namespace Felix\Onboard\Middleware;

use Closure;
use Felix\Onboard\Concerns\GetsOnboarded;
use Felix\Onboard\Step;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use RuntimeException;

class ResumeOnboarding
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        /** @var Authenticatable|null $user */
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (!method_exists($user, 'onboarding')) {
            throw new RuntimeException('The ' . $user::class . ' does not implement ' . GetsOnboarded::class);
        }

        $nextStep = $user->onboarding()->nextUnfinishedStep();

        if ($nextStep === null || $this->isStepPath($request, $nextStep)) {
            return $next($request);
        }

        return $nextStep->redirect();
    }

    public function isStepPath(Request $request, Step $step): bool
    {
        return (parse_url($step->href ?? '', PHP_URL_PATH)) === '/' . $request->path();
    }
}
