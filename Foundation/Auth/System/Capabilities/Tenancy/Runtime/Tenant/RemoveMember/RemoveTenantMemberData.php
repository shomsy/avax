<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember;

final readonly class RemoveTenantMemberData
{
    public int    $userId;
    public string $tenantSlug;

    public function __construct(
        string $tenantSlug,
        int    $userId
    )
    {
        $this->tenantSlug = $tenantSlug;
        $this->userId     = $userId;
    }
}
