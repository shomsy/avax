<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\ReadTenants;

use Avax\Components\Identity\Tenancy\System\Capabilities\Model\Tenant;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantStoreInterface;

final readonly class ReadTenants
{
    public function __construct(private TenantStoreInterface $tenantStore) {}

    /**
     * @return list<Tenant>
     */
    public function execute(): array
    {
        return $this->tenantStore->allTenants();
    }
}
