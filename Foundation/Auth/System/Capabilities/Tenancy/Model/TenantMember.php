<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Model;

use DateTimeImmutable;

final readonly class TenantMember
{
    public DateTimeImmutable $joinedAt;
    public TenantMemberState $state;
    public TenantMemberRole  $role;
    public int               $userId;
    public string            $tenantId;

    public function __construct(
        string            $tenantId,
        int               $userId,
        TenantMemberRole  $role,
        TenantMemberState $state,
        DateTimeImmutable $joinedAt
    )
    {
        $this->tenantId = $tenantId;
        $this->userId   = $userId;
        $this->role     = $role;
        $this->state    = $state;
        $this->joinedAt = $joinedAt;
    }
}
