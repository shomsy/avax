<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember;

final readonly class SuspendTenantMemberData
{
    public function __construct(public string $tenantSlug, public int $userId)
    {
    }
}
