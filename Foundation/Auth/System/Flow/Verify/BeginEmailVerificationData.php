<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Verify;

/**
 * Boundary input for issuing an email verification token.
 */
final readonly class BeginEmailVerificationData
{
    public function __construct(
        public string      $email,
        public string|null $ipAddress = null,
        public string|null $userAgent = null
    ) {}
}
