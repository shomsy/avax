<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\Sessions\Runtime;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Public session activity snapshot exposed by the session subsystem.
 */
final readonly class ActiveSession
{
    public bool $current;

    public function __construct(
        #[SensitiveParameter] public string      $sessionId,
        public DateTimeImmutable                 $createdAt,
        public DateTimeImmutable                 $lastSeenAt,
        public DateTimeImmutable                 $idleExpiresAt,
        public DateTimeImmutable                 $absoluteExpiresAt,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null,
        bool|null                                $current = null,
        public DateTimeImmutable|null            $revokedAt = null,
        public string|null                       $revokeReason = null
    )
    {
        $current       ??= false;
        $this->current = $current;
    }
}
