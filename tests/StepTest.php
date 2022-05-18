<?php

use Felix\Onboard\Step;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->step = new Step('foo');
    $this->step->forUser(new User());
});

it('returns true if the step is complete', function () {
    $isCompleted = false;
    $this->step->href('bar')->completedIf(function () use (&$isCompleted) {
        return $isCompleted;
    });

    expect($this->step->isComplete())->toBe(false);
    expect($this->step->isIncomplete())->toBe(true);
});

it('can resole a route name', function () {
    Route::get('/welcome')->name('welcome');

    $this->step->route('welcome');

    expect($this->step->url())->toBe('http://localhost/welcome');
});

it('can resolve a callable route name', function () {
    Route::get('/welcome/{name}')->name('welcome.name');

    $this->step->route(function () {
        return \route('welcome.name', [
            'name' => 'Taylor',
        ]);
    });

    expect($this->step->url())->toBe('http://localhost/welcome/Taylor');
});

it('is marked as completed if the step is skipped', function () {
    $isSkipped   = false;
    $isCompleted = true;
    $this->step
        ->skipIf(function () use (&$isSkipped) {
            return $isSkipped;
        })
        ->href('/')
        ->completedIf(function () use (&$isCompleted) {
            return $isCompleted;
        });

    expect($this->step->isSkipped())->toBe(false);
    expect($this->step->isComplete())->toBe(true);

    $isSkipped = true;

    expect($this->step->isSkipped())->toBe(true);
    expect($this->step->isComplete())->toBe(true);

    $isCompleted = false;

    expect($this->step->isSkipped())->toBe(true);
    expect($this->step->isComplete())->toBe(true);
});

it('can be converted to json', function () {
    $this->step->completedIf(fn () => false)->href('/something');
    $expected = json_encode($this->step->toArray());

    expect($this->step->toJson())->toBe($expected);
});

it('can redirect to the step', function () {
    $this->step->href('/hello');
    expect($this->step->redirect()->getTargetUrl())->toBe('http://localhost/hello');
});

it('can mark a path as allowed', function () {
    $this->step->allow([
        '/hello/*/something',
    ]);

    $request = createRequest('GET', '/hello/world/something');

    $request->setRouteResolver(function () {
        return Route::get('/hello/world/something', fn () => '');
    });

    expect($this->step->usesRoute($request))->toBeTrue();

    $request = createRequest('GET', '/something-else');
    expect($this->step->usesRoute($request))->toBeFalse();
});

it('can mark a route as allowed', function () {
    $this->step->allowRoutes([
        'test-route',
    ]);

    $request = createRequest('GET', '/test');

    $request->setRouteResolver(function () {
        $route = Route::get('/test-route', fn () => '')->name('test-route');

        return $route;
    });

    expect($this->step->usesRoute($request))->toBeTrue();

    $request = createRequest('GET', '/other-test');
    $request->setRouteResolver(function () {
        return Route::get('/something-else', fn () => '')->name('something-else');
    });
    expect($this->step->usesRoute($request))->toBeFalse();
});

it('marks path as used if it is the step\'s href', function () {
    $this->step->href('/something');

    $request = createRequest('GET', '/something');
    expect($this->step->usesRoute($request))->toBeTrue();

    $request = createRequest('GET', '/something-else');
    expect($this->step->usesRoute($request))->toBeFalse();
});
