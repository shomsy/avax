<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Model;

use DateTimeImmutable;

final readonly class TenantMember
{
    public function __construct(public string $tenantId, public int $userId, public TenantMemberRole $role, public TenantMemberState $state, public DateTimeImmutable $joinedAt) {}
}
