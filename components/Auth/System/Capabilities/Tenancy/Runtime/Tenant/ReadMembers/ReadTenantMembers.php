<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\ReadMembers;

use Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantStoreInterface;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TenantFailed;

final readonly class ReadTenantMembers
{
    public function __construct(private TenantStoreInterface $tenantStore) {}

    /**
     * @return list<TenantMember>
     */
    public function execute(string $tenantSlug) : array
    {
        $tenant = $this->tenantStore->findTenantBySlug(slug: $tenantSlug);

        if ($tenant === null) {
            throw TenantFailed::tenantNotFound(tenantSlug: $tenantSlug);
        }

        return $this->tenantStore->allMembers(tenantId: $tenant->tenantId);
    }
}
