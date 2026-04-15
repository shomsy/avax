<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Session;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Durable server-side session state owned by the session subsystem.
 */
final readonly class SessionRecord
{
    public string|null            $revokeReason;
    public DateTimeImmutable|null $revokedAt;
    public string|null            $userAgentCreated;
    public string|null            $ipCreated;
    public DateTimeImmutable      $absoluteExpiresAt;
    public DateTimeImmutable      $idleExpiresAt;
    public DateTimeImmutable      $lastSeenAt;
    public DateTimeImmutable      $createdAt;
    public UserId                 $userId;
    public string                 $sessionId;

    public function __construct(
        #[SensitiveParameter] string $sessionId,
        UserId                       $userId,
        DateTimeImmutable            $createdAt,
        DateTimeImmutable            $lastSeenAt,
        DateTimeImmutable            $idleExpiresAt,
        DateTimeImmutable            $absoluteExpiresAt,
        string|null                  $ipCreated = null,
        string|null                  $userAgentCreated = null,
        DateTimeImmutable|null       $revokedAt = null,
        string|null                  $revokeReason = null
    )
    {
        $this->sessionId         = $sessionId;
        $this->userId            = $userId;
        $this->createdAt         = $createdAt;
        $this->lastSeenAt        = $lastSeenAt;
        $this->idleExpiresAt     = $idleExpiresAt;
        $this->absoluteExpiresAt = $absoluteExpiresAt;
        $this->ipCreated         = $ipCreated;
        $this->userAgentCreated  = $userAgentCreated;
        $this->revokedAt         = $revokedAt;
        $this->revokeReason      = $revokeReason;
    }

    public function isActiveAt(DateTimeImmutable $moment) : bool
    {
        return ! $this->isRevoked() && ! $this->isExpiredAt(moment: $moment);
    }

    public function isRevoked() : bool
    {
        return $this->revokedAt !== null;
    }

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->idleExpiresAt <= $moment || $this->absoluteExpiresAt <= $moment;
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function withTouch(DateTimeImmutable $lastSeenAt, int $idleTimeoutSeconds) : self
    {
        return new self(
            sessionId        : $this->sessionId,
            userId           : $this->userId,
            createdAt        : $this->createdAt,
            lastSeenAt       : $lastSeenAt,
            idleExpiresAt    : $lastSeenAt->modify(modifier: "+{$idleTimeoutSeconds} seconds"),
            absoluteExpiresAt: $this->absoluteExpiresAt,
            ipCreated        : $this->ipCreated,
            userAgentCreated : $this->userAgentCreated,
            revokedAt        : $this->revokedAt,
            revokeReason     : $this->revokeReason
        );
    }

    public function withClientMetadata(#[SensitiveParameter] string|null $ipAddress, string|null $userAgent) : self
    {
        return new self(
            sessionId        : $this->sessionId,
            userId           : $this->userId,
            createdAt        : $this->createdAt,
            lastSeenAt       : $this->lastSeenAt,
            idleExpiresAt    : $this->idleExpiresAt,
            absoluteExpiresAt: $this->absoluteExpiresAt,
            ipCreated        : $ipAddress ?? $this->ipCreated,
            userAgentCreated : $userAgent ?? $this->userAgentCreated,
            revokedAt        : $this->revokedAt,
            revokeReason     : $this->revokeReason
        );
    }

    public function withRevocation(DateTimeImmutable $revokedAt, string $revokeReason) : self
    {
        return new self(
            sessionId        : $this->sessionId,
            userId           : $this->userId,
            createdAt        : $this->createdAt,
            lastSeenAt       : $this->lastSeenAt,
            idleExpiresAt    : $this->idleExpiresAt,
            absoluteExpiresAt: $this->absoluteExpiresAt,
            ipCreated        : $this->ipCreated,
            userAgentCreated : $this->userAgentCreated,
            revokedAt        : $revokedAt,
            revokeReason     : $revokeReason
        );
    }
}
