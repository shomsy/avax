<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Support;

use DateTimeImmutable;

final readonly class PasskeyChallengeRecord
{
    public function __construct(public string $challengeId, public string $challenge, public PasskeyChallengePurpose $purpose, public DateTimeImmutable $expiresAt, public int|null $userId = null, public DateTimeImmutable|null $usedAt = null) {}

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }

    public function wasUsed() : bool
    {
        return $this->usedAt !== null;
    }
}
