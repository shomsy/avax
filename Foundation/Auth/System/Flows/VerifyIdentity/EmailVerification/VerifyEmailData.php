<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\VerifyIdentity\EmailVerification;

use SensitiveParameter;

/**
 * Boundary input for completing email verification.
 */
final readonly class VerifyEmailData
{
    public function __construct(
        #[SensitiveParameter] public string      $token,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null
    )
    {
    }
}
