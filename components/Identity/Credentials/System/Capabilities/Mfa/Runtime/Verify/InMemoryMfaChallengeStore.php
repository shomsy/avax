<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\PruneExpiredMfaChallengesInterface;
use DateTimeImmutable;

/**
 * In-memory MFA challenge store for tests and demos.
 */
final class InMemoryMfaChallengeStore implements MfaChallengeStoreInterface, PruneExpiredMfaChallengesInterface
{
    /** @var array<string, MfaChallengeRecord> */
    private array $records = [];

    public function issue(MfaChallengeRecord $mfaChallengeRecord) : void
    {
        $this->records[$mfaChallengeRecord->challengeId] = $mfaChallengeRecord;
    }

    public function find(string $challengeId) : ?MfaChallengeRecord
    {
        return $this->records[$challengeId] ?? null;
    }

    public function save(MfaChallengeRecord $mfaChallengeRecord) : void
    {
        $this->records[$mfaChallengeRecord->challengeId] = $mfaChallengeRecord;
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
