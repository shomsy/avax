<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store;

use Avax\Components\Identity\Auth\System\Foundation\Ids\SessionId;
use Avax\Components\Identity\Auth\System\Foundation\Ids\TenantId;
use Avax\Components\Identity\Auth\System\Foundation\Ids\UserId;
use DateTimeImmutable;

/**
 * IdentitySession — value object representing an authenticated user session.
 *
 * Adapted from the enterprise reference package.
 * Immutable; expiresAt determines session lifetime.
 */
final readonly class IdentitySession
{
    public function __construct(
        private SessionId $sessionId,
        private UserId $userId,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $expiresAt,
        private TenantId|null $tenantId = null,
    ) {}

    public function sessionId(): SessionId
    {
        return $this->sessionId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function tenantId(): TenantId|null
    {
        return $this->tenantId;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpiredAt(DateTimeImmutable $time): bool
    {
        return $this->expiresAt <= $time;
    }
}
