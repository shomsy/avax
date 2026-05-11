<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Result of email verification challenge creation.
 */
final readonly class EmailVerificationChallenge
{
    public function __construct(
        public bool               $dispatched,
        #[SensitiveParameter]
        public string|null            $token = null,
        public DateTimeImmutable|null $expiresAt = null,
    ) {}

    public static function hidden() : self
    {
        return new self(dispatched: true);
    }
}
