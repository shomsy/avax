<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime;

use SensitiveParameter;

/**
 * Boundary input for completing MFA verification.
 */
final readonly class VerifyMfaChallengeData
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public string      $code;
    public string      $challengeId;

    public function __construct(
        string                            $challengeId,
        #[SensitiveParameter] string      $code,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->challengeId = $challengeId;
        $this->code        = $code;
        $this->ipAddress   = $ipAddress;
        $this->userAgent   = $userAgent;
    }
}
