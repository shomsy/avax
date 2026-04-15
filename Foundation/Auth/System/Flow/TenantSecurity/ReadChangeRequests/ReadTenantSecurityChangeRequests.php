<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\TenantSecurity\ReadChangeRequests;

use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStoreInterface;

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
