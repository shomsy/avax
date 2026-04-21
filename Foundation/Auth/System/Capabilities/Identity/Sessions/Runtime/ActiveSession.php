<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Sessions\Runtime;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Public session activity snapshot exposed by the session subsystem.
 */
final readonly class ActiveSession
{
    public string|null            $revokeReason;
    public DateTimeImmutable|null $revokedAt;
    public bool                   $current;
    public string|null            $userAgent;
    public string|null            $ipAddress;
    public DateTimeImmutable      $absoluteExpiresAt;
    public DateTimeImmutable      $idleExpiresAt;
    public DateTimeImmutable      $lastSeenAt;
    public DateTimeImmutable      $createdAt;
    public string                 $sessionId;

    public function __construct(
        #[SensitiveParameter] string      $sessionId,
        DateTimeImmutable                 $createdAt,
        DateTimeImmutable                 $lastSeenAt,
        DateTimeImmutable                 $idleExpiresAt,
        DateTimeImmutable                 $absoluteExpiresAt,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null,
        bool|null                         $current = null,
        DateTimeImmutable|null            $revokedAt = null,
        string|null                       $revokeReason = null
    )
    {
        $current                 ??= false;
        $this->sessionId         = $sessionId;
        $this->createdAt         = $createdAt;
        $this->lastSeenAt        = $lastSeenAt;
        $this->idleExpiresAt     = $idleExpiresAt;
        $this->absoluteExpiresAt = $absoluteExpiresAt;
        $this->ipAddress         = $ipAddress;
        $this->userAgent         = $userAgent;
        $this->current           = $current;
        $this->revokedAt         = $revokedAt;
        $this->revokeReason      = $revokeReason;
    }
}
