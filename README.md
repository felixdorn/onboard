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

Add the `ResumeOnboarding` middle at the end of the `web` stack in `app/Http/Kernel.php`.

```php
    // ...

    protected $middlewareGroups = [
        'web' => [
            // ...
            \Felix\Onboard\Middleware\ResumeOnboarding::class
        ]   
    ]
```

Then, make sure that your `User` model uses `GetsOnboarded`

```php
use Felix\Onboard\Concerns\GetsOnboarded;

class User extends Authenticatable {
    use GetsOnboarded;
    
    // ...
}
```

You're all set.

## Usage

In your `app/Providers/AppServiceProvider.php`, add your onboarding steps.

> If you have a lot of steps, you may consider creating an `OnboardServiceProvider` (don't forget to register it in `config/app.php`).

```php
use \App\Models\User;
use \Felix\Onboard\Facades\Onboard;

Onboard::add('verify_email')
    ->completedIf(function (User $user) {
        return $user->hasVerifiedEmail(); // or whatever    
    })
    ->skipIf(function () {
        return $user->github_id != null;
    })
    ->route('verification.notice')
    ->allowRoutes(['verification.verify'])
```

You may pass a closure to resolve a route:

```php
Onboard::add('create_team')
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
