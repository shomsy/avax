<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored pending MFA enrollment state.
 */
final readonly class MfaEnrollmentRecord
{
    public function __construct(
        public UserId                       $userId,
        public MfaMethod                    $method,
        #[SensitiveParameter] public string $accountLabel,
        public string                       $issuer,
        #[SensitiveParameter] public string $secret,
        public DateTimeImmutable            $startedAt,
        public DateTimeImmutable            $expiresAt
    ) {}

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }
}
