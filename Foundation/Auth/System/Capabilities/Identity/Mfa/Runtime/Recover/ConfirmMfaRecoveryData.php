<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover;

use SensitiveParameter;

/**
 * Boundary input for confirming MFA recovery.
 */
final readonly class ConfirmMfaRecoveryData
{
    public function __construct(
        #[SensitiveParameter] public string      $token,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null
    )
    {
    }
}
