<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Mfa\Challenge;

use Avax\Auth\System\Capabilities\User\UserId;
use DateTimeImmutable;

/**
 * In-memory MFA challenge store for tests and demos.
 */
final class InMemoryMfaChallengeStore implements MfaChallengeStoreInterface, PruneExpiredMfaChallengesInterface
{
    /** @var array<string, MfaChallengeRecord> */
    private array $records = [];

    public function issue(MfaChallengeRecord $record) : void
    {
        $this->records[$record->challengeId] = $record;
    }

    public function find(string $challengeId) : MfaChallengeRecord|null
    {
        return $this->records[$challengeId] ?? null;
    }

    public function save(MfaChallengeRecord $record) : void
    {
        $this->records[$record->challengeId] = $record;
    }

    public function forget(string $challengeId) : void
    {
        unset($this->records[$challengeId]);
    }

    public function forgetForUser(UserId $userId) : void
    {
        foreach ($this->records as $challengeId => $record) {
            if ($record->userId->equals(other: $userId)) {
                unset($this->records[$challengeId]);
            }
        }
    }

    public function pruneExpired(DateTimeImmutable $now) : int
    {
        $removed = 0;

        foreach ($this->records as $challengeId => $record) {
            if (! $record->isExpiredAt(moment: $now)) {
                continue;
            }

            unset($this->records[$challengeId]);
            $removed++;
        }

        return $removed;
    }
}
