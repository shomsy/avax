<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Security;

interface TenantSecurityChangeRequestStoreInterface
{
    public function save(TenantSecurityChangeRequest $tenantSecurityChangeRequest) : void;

    public function find(string $changeId) : ?TenantSecurityChangeRequest;

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function allForTenant(string $tenantSlug) : array;
}
