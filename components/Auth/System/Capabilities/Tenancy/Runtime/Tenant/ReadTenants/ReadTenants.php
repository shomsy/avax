<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Tenancy\Runtime\Tenant\ReadTenants;

use components\Auth\System\Capabilities\Tenancy\Model\Tenant;
use components\Auth\System\Capabilities\Tenancy\Model\TenantStoreInterface;

final readonly class ReadTenants
{
    public function __construct(private TenantStoreInterface $tenantStore) {}

    /**
     * @return list<Tenant>
     */
    public function execute() : array
    {
        return $this->tenantStore->allTenants();
    }
}
