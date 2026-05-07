<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Runtime\Tenant\ReadMembers;

use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\Tenant;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\TenantMember;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\System\Capabilities\Runtime\Tenant\TenantFailed;

final readonly class ReadTenantMembers
{
    public function __construct(private TenantStoreInterface $tenantStore) {}

    /**
     * @return list<TenantMember>
     */
    public function execute(string $tenantSlug) : array
    {
        $tenant = $this->tenantStore->findTenantBySlug(slug: $tenantSlug);

        if (! $tenant instanceof Tenant) {
            throw TenantFailed::tenantNotFound(tenantSlug: $tenantSlug);
        }

        return $this->tenantStore->allMembers(tenantId: $tenant->tenantId);
    }
}
