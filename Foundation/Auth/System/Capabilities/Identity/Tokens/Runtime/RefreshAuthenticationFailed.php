<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime;

use Exception;

/**
 * Safe public failure contract for refresh token usage.
 */
class RefreshAuthenticationFailed extends Exception
{
    public static function invalidToken() : self
    {
        return new self(message: 'Refresh token is invalid.', code: 401);
    }
}
