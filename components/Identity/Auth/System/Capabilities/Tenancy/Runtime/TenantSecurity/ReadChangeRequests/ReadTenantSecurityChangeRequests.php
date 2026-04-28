<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ReadChangeRequests;

use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStoreInterface;

final readonly class ReadTenantSecurityChangeRequests
{
    public function __construct(private TenantSecurityChangeRequestStoreInterface $changeRequestStore) {}

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function execute(string $tenantSlug) : array
    {
        return $this->changeRequestStore->allForTenant(tenantSlug: $tenantSlug);
    }
}
