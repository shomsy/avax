<?php

declare(strict_types=1);

namespace components\Auth\System\Flows\Login;

use Exception;

/**
 * Safe login failure contract exposed at the public boundary.
 */
class AuthenticationFailed extends Exception
{
    public static function invalidCredentials() : self
    {
        return new self(message: 'Invalid credentials.', code: 401);
    }
}
