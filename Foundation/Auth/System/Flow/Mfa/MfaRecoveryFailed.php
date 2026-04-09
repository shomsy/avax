<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use RuntimeException;

/**
 * Safe public failure contract for MFA recovery.
 */
final class MfaRecoveryFailed extends RuntimeException
{
    public static function invalidToken() : self
    {
        return new self('MFA recovery token is invalid.', 401);
    }

    public static function expiredToken() : self
    {
        return new self('MFA recovery token has expired.', 410);
    }
}
