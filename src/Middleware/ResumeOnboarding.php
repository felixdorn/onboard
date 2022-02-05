<?php

namespace Felix\Onboard\Middleware;

use Closure;
use Felix\Onboard\Step;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class ResumeOnboarding
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        /** @var Authenticatable|null $user */
        $user = $request->user();

        if (!$user || !method_exists($user, 'onboarding')) {
            return $next($request);
        }

        $currentStep = $user->onboarding()->nextUnfinishedStep();

        if ($currentStep === null || $this->isStep($request, $currentStep)) {
            return $next($request);
        }

        return $currentStep->redirect();
    }

    public function isStep(Request $request, Step $step): bool
    {
        return parse_url($step->url() ?? '', PHP_URL_PATH) === '/' . $request->path();
    }
}
