<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Data;

use SensitiveParameter;

/**
 * Boundary input for completing MFA verification.
 */
final readonly class VerifyMfaChallengeData
{
    public function __construct(
        public string                            $challengeId,
        #[SensitiveParameter] public string      $code,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null
    ) {}
}
