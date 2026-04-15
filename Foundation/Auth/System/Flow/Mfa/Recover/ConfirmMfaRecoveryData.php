<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Recover;

use SensitiveParameter;

/**
 * Boundary input for confirming MFA recovery.
 */
final readonly class ConfirmMfaRecoveryData
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public string      $token;

    public function __construct(
        #[SensitiveParameter] string      $token,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->token     = $token;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }
}
