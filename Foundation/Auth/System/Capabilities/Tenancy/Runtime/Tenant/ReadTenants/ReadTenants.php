<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\ReadTenants;

use Avax\Auth\System\Capabilities\Tenancy\Model\Tenant;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantStoreInterface;

final readonly class ReadTenants
{
    private TenantStoreInterface $tenantStore;

    public function __construct(
        TenantStoreInterface $tenantStore
    )
    {
        $this->tenantStore = $tenantStore;
    }

    /**
     * @return list<Tenant>
     */
    public function execute() : array
    {
        return $this->tenantStore->allTenants();
    }
}
