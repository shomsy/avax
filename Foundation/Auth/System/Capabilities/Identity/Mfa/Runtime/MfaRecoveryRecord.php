<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Mfa;

use Avax\Auth\System\Capabilities\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored MFA recovery token state.
 */
final readonly class MfaRecoveryRecord
{
    public DateTimeImmutable $expiresAt;
    public UserId            $userId;
    public string            $tokenHash;

    public function __construct(
        #[SensitiveParameter] string $tokenHash,
        UserId                       $userId,
        DateTimeImmutable            $expiresAt
    )
    {
        $this->tokenHash = $tokenHash;
        $this->userId    = $userId;
        $this->expiresAt = $expiresAt;
    }

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }
}
