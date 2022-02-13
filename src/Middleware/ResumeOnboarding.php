<?php

namespace Felix\Onboard\Middleware;

use Closure;
use Felix\Onboard\Facades\Onboard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class ResumeOnboarding
{
    protected static $disabled = false;

    public static function disable(): void
    {
        static::$disabled = true;
    }

    public function handle(Request $request, Closure $next): mixed
    {
        if (static::$disabled || $request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        /** @var Authenticatable|null $user */
        $user = $request->user();

        if (!$user || !method_exists($user, 'onboarding') || $user->onboarding()->isFinished()) {
            return $next($request);
        }

        $currentStep = $user->onboarding()->nextUnfinishedStep();

        if ($currentStep->usesRoute($request)) {
            return $next($request);
        }

        return $currentStep->redirect();
    }
}
