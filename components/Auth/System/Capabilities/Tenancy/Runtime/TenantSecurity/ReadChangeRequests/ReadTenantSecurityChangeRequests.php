<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ReadChangeRequests;

use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStoreInterface;

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
