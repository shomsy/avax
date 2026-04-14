<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\RemoveMember;

final readonly class RemoveTenantMemberData
{
    public function __construct(
        public string $tenantSlug,
        public int $userId
    ) {}
}
