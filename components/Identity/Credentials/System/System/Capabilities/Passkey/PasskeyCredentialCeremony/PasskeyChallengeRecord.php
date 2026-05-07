<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony;

use DateTimeImmutable;

final readonly class PasskeyChallengeRecord
{
    public function __construct(public string $challengeId, public string $challenge, public PasskeyChallengePurpose $purpose, public DateTimeImmutable $expiresAt, public ?int $userId = null, public ?DateTimeImmutable $usedAt = null) {}

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }

    public function wasUsed() : bool
    {
        return $this->usedAt instanceof DateTimeImmutable;
    }
}
