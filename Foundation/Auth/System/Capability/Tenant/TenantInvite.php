<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Tenant;

use DateTimeImmutable;

final readonly class TenantInvite
{
    public function __construct(
        public string $inviteId,
        public string $tenantId,
        public string $email,
        public TenantMemberRole $role,
        public string $tokenHash,
        public string $invitedBy,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable|null $acceptedAt = null,
        public int|null $acceptedByUserId = null
    ) {}

    public function isAccepted() : bool
    {
        return $this->acceptedAt !== null;
    }
}
