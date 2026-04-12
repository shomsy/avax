<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Passkey;

use DateTimeImmutable;

final class InMemoryPasskeyChallengeStore implements PasskeyChallengeStoreInterface, PruneExpiredPasskeyChallengesInterface
{
    /** @var array<string, PasskeyChallengeRecord> */
    private array $records = [];

    public function issue(PasskeyChallengeRecord $record) : void
    {
        $this->records[$record->challengeId] = $record;
    }

    public function find(string $challengeId) : PasskeyChallengeRecord|null
    {
        return $this->records[$challengeId] ?? null;
    }

    public function markUsed(string $challengeId, DateTimeImmutable $usedAt) : void
    {
        $record = $this->records[$challengeId] ?? null;

        if ($record === null) {
            return;
        }

        $this->records[$challengeId] = new PasskeyChallengeRecord(
            challengeId: $record->challengeId,
            challenge  : $record->challenge,
            purpose    : $record->purpose,
            expiresAt  : $record->expiresAt,
            userId     : $record->userId,
            usedAt     : $usedAt
        );
    }

    public function forget(string $challengeId) : void
    {
        unset($this->records[$challengeId]);
    }

    public function pruneExpired(DateTimeImmutable $now) : int
    {
        $removed = 0;

        foreach ($this->records as $challengeId => $record) {
            if (! $record->wasUsed() && ! $record->isExpiredAt($now)) {
                continue;
            }

            unset($this->records[$challengeId]);
            $removed++;
        }

        return $removed;
    }
}
