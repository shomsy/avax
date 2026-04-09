<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Login;

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
