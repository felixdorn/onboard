<?php

use Felix\Onboard\Exceptions\StepCanNeverBeCompletedException;
use Felix\Onboard\Step;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->step = new Step('foo');
    $this->step->user(new User());
});

it('throws an error if href is empty', function () {
    $this->step->isComplete();
})->throws(StepCanNeverBeCompletedException::class);

it('throws an error if isCompleted is empty', function () {
    $this->step->href('/bar');

    $this->step->isComplete();
})->throws(StepCanNeverBeCompletedException::class);

it('returns true if the step is complete', function () {
    $isCompleted = false;
    $this->step->href('bar')->completedIf(function () use (&$isCompleted) {
        return $isCompleted;
    });

    expect($this->step->isComplete())->toBe(false);
    expect($this->step->isIncomplete())->toBe(true);

    $isCompleted = true;

    expect($this->step->isComplete())->toBe(true);
    expect($this->step->isIncomplete())->toBe(false);
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
    $isSkipped = false;
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
