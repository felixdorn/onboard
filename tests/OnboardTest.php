<?php

use Felix\Onboard\Onboard;
use Illuminate\Foundation\Auth\User;

beforeEach(function () {
    $this->onboard = new Onboard(new User());
});

it('adds a step', function () {
    $this->onboard->add('Complete Profile')->href('/something')->completedIf(fn () => false);

    expect($this->onboard->nextUnfinishedStep())
        ->name->toBe('Complete Profile')
        ->href->toBe('/something');
});

it('returns the next unfinished step', function ($name, $steps) {
    foreach ($steps as $step) {
        $this->onboard->add($step[0])->href($step[1])->completedIf($step[2]);
    }

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

    $this->onboard->add('Step 1')->href('')->completedIf(function () use (&$isCompleted) {
        return $isCompleted;
    });

    expect($this->onboard->isFinished())->toBe(false);
    expect($this->onboard->inProgress())->toBe(true);

    $isCompleted = true;

    expect($this->onboard->isFinished())->toBe(true);
    expect($this->onboard->inProgress())->toBe(false);
});

it('can return the steps', function () {
    $this->onboard->add('Step 1')->href('step-1')->completedIf(fn () => true);
    $this->onboard->add('Step 2')->href('step-2')->completedIf(fn () => true);

    expect($this->onboard->steps())->sequence(
        fn ($step) => $step
            ->name->toBe('Step 1')
            ->href->toBe('step-1'),
        fn ($step) => $step
            ->name->toBe('Step 2')
            ->href->toBe('step-2')
    );
});

it('can be converted to an array', function () {
    $this->onboard->add('Step 1')->href('step-1')->completedIf(fn () => true);
    $this->onboard->add('Step 2')->href('step-2')->completedIf(fn () => true);

    expect($this->onboard->toArray())->toBe([
        [
            'name'      => 'Step 1',
            'href'      => 'step-1',
            'completed' => true,
            'context'   => [],
        ],
        [
            'name'      => 'Step 2',
            'href'      => 'step-2',
            'completed' => true,
            'context'   => [],
        ],
    ]);
});
it('can be converted to json', function () {
    $this->onboard->add('Step 1')->href('step-1')->completedIf(fn () => true);
    $this->onboard->add('Step 2')->href('step-2')->completedIf(fn () => true);

    $expected = json_encode([
        [
            'name'      => 'Step 1',
            'href'      => 'step-1',
            'completed' => true,
            'context'   => [],
        ],
        [
            'name'      => 'Step 2',
            'href'      => 'step-2',
            'completed' => true,
            'context'   => [],
        ],
    ]);

    expect($this->onboard->toJson())->toBe($expected);
});
