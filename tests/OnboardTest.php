<?php

use Felix\Onboard\Exceptions\CompletedCallableReturnsNonBoolean;
use Felix\Onboard\Onboard;
use Felix\Onboard\Step;
use Felix\Onboard\StepsCache;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->memory  = new StepsCache();
    $this->onboard = new Onboard(new User());
});

it('adds a step', function () {
    $this->memory->add('Complete Profile')->href('/something')->completedIf(fn () => false);

    $this->onboard->steps = $this->memory->steps;
    expect($this->onboard->nextUnfinishedStep())
        ->name->toBe('Complete Profile')
        ->url()->toBe('http://localhost/something');
});

it('returns the next unfinished step', function ($name, $steps) {
    foreach ($steps as $step) {
        $this->memory->add($step[0])->href($step[1])->completedIf($step[2]);
    }

    $this->onboard->steps = $this->memory->steps;
    expect($this->onboard->nextUnfinishedStep()?->name)->toBe($name);
})->with([
    [
        null,
        [],
    ],
    [
        'Add 2FA',
        [
            ['First', '/first', fn () => true],
            ['Add 2FA', '/2fa', fn () => false],
            ['Add something else', '/something', fn () => false],
        ],
    ],

    [
        null,
        [
            ['First', '/first', fn () => true],
            ['Add 2FA', '/2fa', fn () => true],
            ['Add something else', '/something', fn () => true],
        ],
    ],
]);

it('marks an onboarding as unfinished', function () {
    $isCompleted = false;

    $this->memory->add('Step 1')->href('')->completedIf(function () use (&$isCompleted) {
        return $isCompleted;
    });
    $this->onboard->steps = $this->memory->steps;

    expect($this->onboard->isFinished())->toBe(false);
    expect($this->onboard->inProgress())->toBe(true);
});

it('can be converted to an array', function () {
    $this->memory->add('Step 1')->href('step-1')->completedIf(fn () => true);
    $this->memory->add('Step 2')->href('step-2')->completedIf(fn () => false);
    $this->memory->add('Step 3')->href('step-3')->completedIf(fn () => false)->skipIf(fn () => true);
    $this->onboard->steps = $this->memory->steps;

    expect($this->onboard->toArray())->toBe([
        'finished' => 2,
        'total'    => 3,
        'current'  => [
            'name'      => 'Step 2',
            'href'      => 'http://localhost/step-2',
            'completed' => false,
            'skipped'   => false,
        ],
        'current_index' => 1,
        'steps'         => [
            [
                'name'      => 'Step 1',
                'href'      => 'http://localhost/step-1',
                'completed' => true,
                'skipped'   => false,
            ],
            [
                'name'      => 'Step 2',
                'href'      => 'http://localhost/step-2',
                'completed' => false,
                'skipped'   => false,
            ],
            [
                'name'      => 'Step 3',
                'href'      => 'http://localhost/step-3',
                'completed' => true,
                'skipped'   => true,
            ],
        ],
    ]);
});
it('can be converted to json', function () {
    $this->memory->add('Step 1')->href('step-1')->completedIf(fn () => true);
    $this->memory->add('Step 2')->href('step-2')->completedIf(fn () => true);
    $this->onboard->steps = $this->memory->steps;

    $expected = json_encode([
        'finished'      => 2,
        'total'         => 2,
        'current'       => null,
        'current_index' => 1,
        'steps'         => [
            [
                'name'      => 'Step 1',
                'href'      => 'http://localhost/step-1',
                'completed' => true,
                'skipped'   => false,
            ],
            [
                'name'      => 'Step 2',
                'href'      => 'http://localhost/step-2',
                'completed' => true,
                'skipped'   => false,
            ],
        ],
    ]);

    expect($this->onboard->toJson())->toBe($expected);
});

it('can return the current progress of an onboarding', function ($n, $percent) {
    for ($i = 0; $i < 10; $i++) {
        $this->memory->add('Step ' . $i)->href('/step/' . $i)->completedIf(fn () => $i < $n);
    }

    $this->onboard->steps = $this->memory->steps;

    expect($this->onboard->progress())->toBe($percent);
})->with([
    [0, 0.0],
    [1, 10.0],
    [2, 20.0],
    [10, 100.0],
]);

it('can allow a route globally', function () {
    $this->memory->allowRoutes(['logout']);
    $step = $this->memory->add('Step 1');

    $this->onboard->steps = $this->memory->steps;

    $request = createRequest('GET', '/logout');

    $request->setRouteResolver(function () {
        return Route::get('/logout', fn () => '')->name('logout');
    });

    expect($step->usesRoute($request))->toBeTrue();
});
it('can allow a path globally', function () {
    $this->memory->allow(['/settings/billing']);
    $step = $this->memory->add('Step 1');

    $this->onboard->steps = $this->memory->steps;

    $request = createRequest('GET', '/settings/billing');

    $request->setRouteResolver(function () {
        return Route::get('/settings/billing', fn () => '');
    });

    expect($step->usesRoute($request))->toBeTrue();
});

it('memoize the completed callable', function () {
    $counter = 0;

    $step = (new Step('Step 1'))
        ->forUser(new User())
        ->completedIf(function () use (&$counter) {
            $counter++;

            return false;
        });

    $step->isComplete();
    $step->isComplete();

    expect($counter)->toBe(1);
});

it('throws an error if completed if does not return a boolean', function () {
    $step = (new Step('Step 1'))
        ->forUser(new User())
        ->completedIf(fn () => 'd');

    $step->isComplete();
})->throws(CompletedCallableReturnsNonBoolean::class);
