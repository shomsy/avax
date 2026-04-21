<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models;

use RuntimeException;

/**
 * Safe public failure contract for MFA recovery.
 */
final class MfaRecoveryFailed extends RuntimeException
{
    public static function invalidToken() : self
    {
        return new self(message: 'MFA recovery token is invalid.', code: 401);
    }

    public static function expiredToken() : self
    {
        return new self(message: 'MFA recovery token has expired.', code: 410);
    }
}
