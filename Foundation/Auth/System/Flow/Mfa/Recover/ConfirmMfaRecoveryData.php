<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Recover;

use SensitiveParameter;

/**
 * Boundary input for confirming MFA recovery.
 */
final readonly class ConfirmMfaRecoveryData
{
    public function __construct(
        #[SensitiveParameter] public string $token,
        public string|null                  $ipAddress = null,
        public string|null                  $userAgent = null
    ) {}
}
