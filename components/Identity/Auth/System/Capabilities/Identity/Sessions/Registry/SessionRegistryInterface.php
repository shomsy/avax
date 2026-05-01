<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;

/**
 * Stores durable session ownership and revocation state.
 */
interface SessionRegistryInterface
{
    public function track(SessionRecord $record): void;

    public function find(string $sessionId): ?SessionRecord;

    public function save(SessionRecord $record): void;

    /**
     * @return list<SessionRecord>
     */
    public function listForUser(UserId $userId): array;

    public function revoke(string $sessionId, DateTimeImmutable $revokedAt, string $reason): void;

    public function revokeForUser(UserId $userId, DateTimeImmutable $revokedAt, string $reason): void;
}
