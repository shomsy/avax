<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Challenge;

use Avax\Auth\System\Capability\User\UserId;

/**
 * In-memory MFA challenge store for tests and demos.
 */
final class InMemoryMfaChallengeStore implements MfaChallengeStoreInterface
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
            if ($record->userId->equals($userId)) {
                unset($this->records[$challengeId]);
            }
        }
    }
}
