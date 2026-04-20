<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Passkey;

use DateTimeImmutable;

final readonly class PasskeyChallengeRecord
{
    public DateTimeImmutable|null  $usedAt;
    public int|null                $userId;
    public DateTimeImmutable       $expiresAt;
    public PasskeyChallengePurpose $purpose;
    public string                  $challenge;
    public string                  $challengeId;

    public function __construct(
        string                  $challengeId,
        string                  $challenge,
        PasskeyChallengePurpose $purpose,
        DateTimeImmutable       $expiresAt,
        int|null                $userId = null,
        DateTimeImmutable|null  $usedAt = null
    )
    {
        $this->challengeId = $challengeId;
        $this->challenge   = $challenge;
        $this->purpose     = $purpose;
        $this->expiresAt   = $expiresAt;
        $this->userId      = $userId;
        $this->usedAt      = $usedAt;
    }

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }

    public function wasUsed() : bool
    {
        return $this->usedAt !== null;
    }
}
