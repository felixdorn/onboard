<?php

namespace Felix\Onboard\Exceptions;

use Exception;
use Felix\Onboard\Concerns\HasOnboarding;
use Illuminate\Contracts\Auth\Authenticatable;

class UserDoesNotUseOnboarding extends Exception
{
    public function __construct(Authenticatable $user)
    {
        parent::__construct($user::class . ' must use the ' . HasOnboarding::class . ' trait.');
    }
}
