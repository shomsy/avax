<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\VerifyIdentity\EmailVerification;

use SensitiveParameter;

/**
 * Boundary input for completing email verification.
 */
final readonly class VerifyEmailData
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
