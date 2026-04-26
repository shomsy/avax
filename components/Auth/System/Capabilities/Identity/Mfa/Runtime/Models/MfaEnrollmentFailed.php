<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Identity\Mfa\Runtime\Models;

use RuntimeException;

/**
 * Safe public failure contract for MFA enrollment.
 */
final class MfaEnrollmentFailed extends RuntimeException
{
    public static function alreadyEnabled() : self
    {
        return new self(message: 'MFA is already enabled.', code: 409);
    }

    public static function missingEnrollment() : self
    {
        return new self(message: 'MFA enrollment is missing.', code: 404);
    }

    public static function expiredEnrollment() : self
    {
        return new self(message: 'MFA enrollment has expired.', code: 410);
    }

    public static function invalidCode() : self
    {
        return new self(message: 'MFA code is invalid.', code: 422);
    }
}
