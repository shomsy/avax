<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\ReadTenants;

use Avax\Auth\System\Capabilities\Tenancy\Model\Tenant;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantStoreInterface;

final readonly class ReadTenants
{
    public function __construct(private TenantStoreInterface $tenantStore)
    {
    }

    /**
     * @return list<Tenant>
     */
    public function execute() : array
    {
        return $this->tenantStore->allTenants();
    }
}
