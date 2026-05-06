<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Data;

use SensitiveParameter;

/**
 * Boundary input for completing MFA verification.
 */
final readonly class VerifyMfaChallengeData
{
    public function __construct(
        public string $challengeId,
        #[SensitiveParameter]
        public string $code,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {
    }
}
