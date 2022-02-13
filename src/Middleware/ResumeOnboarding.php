<?php

namespace Felix\Onboard\Middleware;

use Closure;
use Felix\Onboard\Exceptions\UserDoesNotUseOnboarding;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResumeOnboarding
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!Auth::check() || $this->disable() || $this->skip($request)) {
            return $next($request);
        }

        /** @var Authenticatable $user */
        $user = $request->user();

        if (!method_exists($user, 'onboarding')) {
            throw new UserDoesNotUseOnboarding($user);
        }

        if ($user->onboarding()->isFinished()) {
            return $next($request);
        }

        $currentStep = $user->onboarding()->nextUnfinishedStep();

        if ($currentStep->usesRoute($request)) {
            return $next($request);
        }

        return $currentStep->redirect();
    }

    public function disable(): bool
    {
        return false;
    }

    public function skip(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }
}
