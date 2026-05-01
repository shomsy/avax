<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification;

use SensitiveParameter;

/**
 * Boundary input for issuing an email verification token.
 */
final readonly class BeginEmailVerificationData
{
    public function __construct(
        #[SensitiveParameter]
        public string $email,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}
