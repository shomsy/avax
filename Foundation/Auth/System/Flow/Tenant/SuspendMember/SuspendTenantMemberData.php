<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\SuspendMember;

final readonly class SuspendTenantMemberData
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
