<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony;

use DateTimeImmutable;

final class InMemoryPasskeyChallengeStore implements PasskeyChallengeStoreInterface, PruneExpiredPasskeyChallengesInterface
{
    /** @var array<string, PasskeyChallengeRecord> */
    private array $records = [];

    public function issue(PasskeyChallengeRecord $passkeyChallengeRecord): void
    {
        $this->records[$passkeyChallengeRecord->challengeId] = $passkeyChallengeRecord;
    }

    public function find(string $challengeId): ?PasskeyChallengeRecord
    {
        return $this->records[$challengeId] ?? null;
    }

    public function markUsed(string $challengeId, DateTimeImmutable $usedAt): void
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
            usedAt     : $usedAt,
        );
    }

    public function forget(string $challengeId): void
    {
        unset($this->records[$challengeId]);
    }

    public function pruneExpired(DateTimeImmutable $now): int
    {
        $removed = 0;

        foreach ($this->records as $challengeId => $record) {
            if (! $record->wasUsed() && ! $record->isExpiredAt(moment: $now)) {
                continue;
            }

            unset($this->records[$challengeId]);
            $removed++;
        }

        return $removed;
    }
}
