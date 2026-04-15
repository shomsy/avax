<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\ReadMembers;

use Avax\Auth\System\Capability\Tenant\TenantMember;
use Avax\Auth\System\Capability\Tenant\TenantStoreInterface;
use Avax\Auth\System\Flow\Tenant\TenantFailed;

final readonly class ReadTenantMembers
{
    private TenantStoreInterface $tenantStore;

    public function __construct(
        TenantStoreInterface $tenantStore
    )
    {
        $this->tenantStore = $tenantStore;
    }

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
