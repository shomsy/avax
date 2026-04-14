<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\SuspendMember;

final readonly class SuspendTenantMemberData
{
    public function __construct(
        public string $tenantSlug,
        public int $userId
    ) {}
}
