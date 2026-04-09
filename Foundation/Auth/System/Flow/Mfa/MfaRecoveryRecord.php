<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

/**
 * Stored MFA recovery token state.
 */
final readonly class MfaRecoveryRecord
{
    public function __construct(
        public string            $tokenHash,
        public UserId            $userId,
        public DateTimeImmutable $expiresAt
    ) {}

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }
}
