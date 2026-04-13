<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Session;

use DateTimeImmutable;

/**
 * Public session activity snapshot exposed by the session subsystem.
 */
final readonly class ActiveSession
{
    public function __construct(
        #[\SensitiveParameter] public string      $sessionId,
        public DateTimeImmutable                  $createdAt,
        public DateTimeImmutable                  $lastSeenAt,
        public DateTimeImmutable                  $idleExpiresAt,
        public DateTimeImmutable                  $absoluteExpiresAt,
        #[\SensitiveParameter] public string|null $ipAddress = null,
        public string|null                        $userAgent = null,
        public bool                               $current = false,
        public DateTimeImmutable|null             $revokedAt = null,
        public string|null                        $revokeReason = null
    ) {}
}
