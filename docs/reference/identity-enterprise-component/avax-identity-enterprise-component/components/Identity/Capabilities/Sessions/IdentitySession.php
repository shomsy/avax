<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Sessions;

use Avax\Components\Identity\Foundation\Values\SessionId;
use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;
use DateTimeImmutable;

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

    public function isExpiredAt(DateTimeImmutable $time): bool
    {
        return $this->expiresAt <= $time;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
