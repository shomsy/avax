<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover;

use SensitiveParameter;

/**
 * Boundary input for starting MFA recovery.
 */
final readonly class BeginMfaRecoveryData
{
    public function __construct(
        #[SensitiveParameter] public string      $email,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null
    ) {}
}
