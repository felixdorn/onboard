# Onboard for Laravel

[![Tests](https://github.com/felixdorn/onboard/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/felixdorn/onboard/actions/workflows/tests.yml)
[![Formats](https://github.com/felixdorn/onboard/actions/workflows/formats.yml/badge.svg?branch=main)](https://github.com/felixdorn/onboard/actions/workflows/formats.yml)
[![Version](https://poser.pugx.org/felixdorn/onboard/version)](//packagist.org/packages/felixdorn/onboard)
[![Total Downloads](https://poser.pugx.org/felixdorn/onboard/downloads)](//packagist.org/packages/felixdorn/onboard)
[![License](https://poser.pugx.org/felixdorn/onboard/license)](//packagist.org/packages/felixdorn/onboard)

## Installation

> Requires [PHP 8.1+](https://php.net/releases) and Laravel 8.x or 9.x

You can install the package via composer:

```bash
composer require felixdorn/onboard
```

Publish the `ResumeOnboarding` middleware to your `app/Http/Middleware` directory:

```bash
php artisan onboard:middleware
```

Add it to your `app/Http/Kernel.php`:

```php
    // ...

    protected $middlewareGroups = [
        'web' => [
            // ...
            \App\Http\Middleware\ResumeOnboarding::class
        ]   
    ]
```

Then, make sure that your `User` model uses `HasOnboarding`

```php
use Felix\Onboard\Concerns\HasOnboarding;

class User extends Authenticatable {
    use HasOnboarding;
    
    // ...
}
```

You're all set.s

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

## Globally allowed routes

Regardless of the state of the onboarding process, you may want the user to have access to certain urls, such as a
logout page.

```php
Onboard::allow('/logout', '/settings/billing');
// or
Onboard::allowRoutes('logout', 'settings.billing')
```

## Testing

```bash
composer test
```

**Onboard for Laravel** was created by **[Félix Dorn](https://twitter.com/afelixdorn)** under
the **[MIT license](https://opensource.org/licenses/MIT)**.
