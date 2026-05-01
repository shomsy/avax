<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Public session activity snapshot exposed by the session subsystem.
 */
final readonly class ActiveSession
{
    public bool $current;

    public function __construct(
        #[SensitiveParameter]
        public string $sessionId,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $lastSeenAt,
        public DateTimeImmutable $idleExpiresAt,
        public DateTimeImmutable $absoluteExpiresAt,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        bool $current = null,
        public ?DateTimeImmutable $revokedAt = null,
        public ?string $revokeReason = null,
    ) {
        $current ??= false;
        $this->current = $current;
    }
}
