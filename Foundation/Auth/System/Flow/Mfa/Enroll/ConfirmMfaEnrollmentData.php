<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Enroll;

use SensitiveParameter;

/**
 * Boundary input for completing MFA enrollment.
 */
final readonly class ConfirmMfaEnrollmentData
{
    public string $code;

    public function __construct(
        #[SensitiveParameter] string $code
    )
    {
        $this->code = $code;
    }
}
