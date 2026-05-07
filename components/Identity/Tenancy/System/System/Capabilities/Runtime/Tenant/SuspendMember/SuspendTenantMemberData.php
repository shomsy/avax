<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Runtime\Tenant\SuspendMember;

final readonly class SuspendTenantMemberData
{
    public function __construct(public string $tenantSlug, public int $userId) {}
}
