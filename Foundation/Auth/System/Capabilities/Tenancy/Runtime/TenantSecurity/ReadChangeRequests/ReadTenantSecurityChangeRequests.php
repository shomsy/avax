<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\TenantSecurity\ReadChangeRequests;

use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityChangeRequestStoreInterface;

final readonly class ReadTenantSecurityChangeRequests
{
    private TenantSecurityChangeRequestStoreInterface $changeRequestStore;

    public function __construct(
        TenantSecurityChangeRequestStoreInterface $changeRequestStore
    )
    {
        $this->changeRequestStore = $changeRequestStore;
    }

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function execute(string $tenantSlug) : array
    {
        return $this->changeRequestStore->allForTenant(tenantSlug: $tenantSlug);
    }
}
