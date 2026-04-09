<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Recover;

use SensitiveParameter;

/**
 * Boundary input for starting MFA recovery.
 */
final readonly class BeginMfaRecoveryData
{
    public function __construct(
        #[SensitiveParameter] public string $email,
        public string|null                  $ipAddress = null,
        public string|null                  $userAgent = null
    ) {}
}
