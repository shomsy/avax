<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\VerifyIdentity\EmailVerification;

use SensitiveParameter;

/**
 * Boundary input for issuing an email verification token.
 */
final readonly class BeginEmailVerificationData
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public string      $email;

    public function __construct(
        #[SensitiveParameter] string      $email,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->email     = $email;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }
}
