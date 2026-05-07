<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Foundation;

use DateTimeImmutable;

final readonly class SessionRecord
{
    public function __construct(
        public string             $sessionId,
        public DateTimeImmutable  $createdAt,
        public DateTimeImmutable  $lastSeenAt,
        public DateTimeImmutable  $idleExpiresAt,
        public DateTimeImmutable  $absoluteExpiresAt,
        public ?string            $userId = null,
        public ?string            $ipCreated = null,
        public ?string            $userAgentCreated = null,
        public ?DateTimeImmutable $revokedAt = null,
        public ?string            $revokeReason = null,
    ) {}

    public function isExpired() : bool
    {
        $now = new DateTimeImmutable();

        return $this->revokedAt instanceof DateTimeImmutable
            || $this->idleExpiresAt <= $now
            || $this->absoluteExpiresAt <= $now;
    }
}
