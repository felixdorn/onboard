# Onboard for Laravel

[![Tests](https://github.com/felixdorn/onboard/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/felixdorn/onboard/actions/workflows/tests.yml)
[![Formats](https://github.com/felixdorn/onboard/actions/workflows/formats.yml/badge.svg?branch=main)](https://github.com/felixdorn/onboard/actions/workflows/formats.yml)
[![Version](https://poser.pugx.org/felixdorn/onboard/version)](//packagist.org/packages/felixdorn/onboard)
[![Total Downloads](https://poser.pugx.org/felixdorn/onboard/downloads)](//packagist.org/packages/felixdorn/onboard)
[![License](https://poser.pugx.org/felixdorn/onboard/license)](//packagist.org/packages/felixdorn/onboard)

## Installation

> Requires [PHP 8.1+](https://php.net/releases)

You can install the package via composer:

```bash
composer require felixdorn/onboard
```

Then, add the `ResumeOnboarding` middle at the end of the `web` stack in `app/Http/Kernel.php`.

```php
    // ...

    protected $middlewareGroups = [
        'web' => [
            // ...
            \Felix\Onboard\Middleware\ResumeOnboarding::class
        ]   
    ]
```

You're all set.

## Usage

```php
use \App\Models\User;
use \Felix\Onboard\Facades\Onboard;

Onboard::add('Step 1')
    ->completedIf(function (User $user) {
        return $user->hasVerifiedEmail(); // or whatever    
    })
    ->skipIf(function () {
        return $user->github_id != null;
    })
    ->route('user.verify', ['foo' => 'bar'])
```

You may pass a closure to resolve a route like shown below.

```php
Onboard::add('Step 2')
    ->route(function () {
        return route('teams.create', [
            'users' => User::query->select('id', 'name', 'email', 'avatar')->get()
        ])  
    })
```

## Testing

```bash
composer test
```

**Onboard for Laravel** was created by **[Félix Dorn](https://twitter.com/afelixdorn)** under
the **[MIT license](https://opensource.org/licenses/MIT)**.
