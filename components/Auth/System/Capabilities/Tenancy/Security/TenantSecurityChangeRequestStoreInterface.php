<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Tenancy\Security;

interface TenantSecurityChangeRequestStoreInterface
{
    public function save(TenantSecurityChangeRequest $changeRequest) : void;

    public function find(string $changeId) : TenantSecurityChangeRequest|null;

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function allForTenant(string $tenantSlug) : array;
}
