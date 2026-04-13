<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Session;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * In-memory session registry for tests and lightweight deployments.
 */
final class InMemorySessionRegistry implements SessionRegistryInterface, PruneExpiredSessionsInterface
{
    /** @var array<string, SessionRecord> */
    private array $records = [];

    public function track(SessionRecord $record) : void
    {
        $this->records[$record->sessionId] = $record;
    }

    public function find(#[SensitiveParameter] string $sessionId) : SessionRecord|null
    {
        return $this->records[$sessionId] ?? null;
    }

    public function save(SessionRecord $record) : void
    {
        $this->records[$record->sessionId] = $record;
    }

    public function listForUser(UserId $userId) : array
    {
        $records = [];

        foreach ($this->records as $record) {
            if ($record->userId->equals(other: $userId)) {
                $records[] = $record;
            }
        }

        usort(
            $records,
            static fn (SessionRecord $left, SessionRecord $right) : int => $right->lastSeenAt <=> $left->lastSeenAt
        );

        return $records;
    }

    public function revoke(#[SensitiveParameter] string $sessionId, DateTimeImmutable $revokedAt, string $reason) : void
    {
        $record = $this->records[$sessionId] ?? null;

        if ($record === null || $record->isRevoked()) {
            return;
        }

        $this->records[$sessionId] = $record->withRevocation(revokedAt: $revokedAt, revokeReason: $reason);
    }

    public function revokeForUser(UserId $userId, DateTimeImmutable $revokedAt, string $reason) : void
    {
        foreach ($this->records as $sessionId => $record) {
            if (! $record->userId->equals(other: $userId) || $record->isRevoked()) {
                continue;
            }

            $this->records[$sessionId] = $record->withRevocation(revokedAt: $revokedAt, revokeReason: $reason);
        }
    }

    public function pruneExpired(DateTimeImmutable $now) : int
    {
        $removed = 0;

        foreach ($this->records as $sessionId => $record) {
            if ($record->isRevoked() || $record->isExpiredAt(moment: $now)) {
                unset($this->records[$sessionId]);
                $removed++;
            }
        }

        return $removed;
    }
}
