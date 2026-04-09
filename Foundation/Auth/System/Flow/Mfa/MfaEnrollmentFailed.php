<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use RuntimeException;

/**
 * Safe public failure contract for MFA enrollment.
 */
final class MfaEnrollmentFailed extends RuntimeException
{
    public static function alreadyEnabled() : self
    {
        return new self('MFA is already enabled.', 409);
    }

    public static function missingEnrollment() : self
    {
        return new self('MFA enrollment is missing.', 404);
    }

    public static function expiredEnrollment() : self
    {
        return new self('MFA enrollment has expired.', 410);
    }

    public static function invalidCode() : self
    {
        return new self('MFA code is invalid.', 422);
    }
}
