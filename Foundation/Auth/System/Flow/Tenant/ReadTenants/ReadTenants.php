<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\ReadTenants;

use Avax\Auth\System\Capability\Tenant\Tenant;
use Avax\Auth\System\Capability\Tenant\TenantStoreInterface;

final readonly class ReadTenants
{
    public function __construct(
        private TenantStoreInterface $tenantStore
    ) {}

    /**
     * @return list<Tenant>
     */
    public function execute() : array
    {
        return $this->tenantStore->allTenants();
    }
}
