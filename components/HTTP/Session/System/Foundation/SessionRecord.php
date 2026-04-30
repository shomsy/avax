<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Foundation;

use DateTimeImmutable;

final readonly class SessionRecord
{
    public function __construct(
        public string            $sessionId,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $lastSeenAt,
        public DateTimeImmutable $idleExpiresAt,
        public DateTimeImmutable $absoluteExpiresAt,
        public string|null            $userId = null,
        public string|null            $ipCreated = null,
        public string|null            $userAgentCreated = null,
        public DateTimeImmutable|null $revokedAt = null,
        public string|null            $revokeReason = null,
    ) {}

    public function isExpired() : bool
    {
        $now = new DateTimeImmutable();

        return $this->revokedAt !== null
            || $this->idleExpiresAt <= $now
            || $this->absoluteExpiresAt <= $now;
    }
}
