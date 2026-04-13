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
    public function __construct(
        #[SensitiveParameter] public string $sessionId,
        public UserId                       $userId,
        public DateTimeImmutable            $createdAt,
        public DateTimeImmutable            $lastSeenAt,
        public DateTimeImmutable            $idleExpiresAt,
        public DateTimeImmutable            $absoluteExpiresAt,
        public string|null                  $ipCreated = null,
        public string|null                  $userAgentCreated = null,
        public DateTimeImmutable|null       $revokedAt = null,
        public string|null                  $revokeReason = null
    ) {}

    public function isRevoked() : bool
    {
        return $this->revokedAt !== null;
    }

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->idleExpiresAt <= $moment || $this->absoluteExpiresAt <= $moment;
    }

    public function isActiveAt(DateTimeImmutable $moment) : bool
    {
        return ! $this->isRevoked() && ! $this->isExpiredAt(moment: $moment);
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
