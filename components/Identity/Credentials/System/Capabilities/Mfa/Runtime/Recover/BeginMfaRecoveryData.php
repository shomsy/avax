<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover;

use SensitiveParameter;

/**
 * Boundary input for starting MFA recovery.
 */
final readonly class BeginMfaRecoveryData
{
    public function __construct(
        #[SensitiveParameter]
        public string $email,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}
