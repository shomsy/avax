<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Security;

final class InMemoryTenantSecurityChangeRequestStore implements TenantSecurityChangeRequestStoreInterface
{
    /** @var array<string, TenantSecurityChangeRequest> */
    private array $changeRequests = [];

    public function save(TenantSecurityChangeRequest $tenantSecurityChangeRequest) : void
    {
        $this->changeRequests[$tenantSecurityChangeRequest->changeId] = $tenantSecurityChangeRequest;
    }

    public function find(string $changeId) : ?TenantSecurityChangeRequest
    {
        return $this->changeRequests[$changeId] ?? null;
    }

    public function allForTenant(string $tenantSlug) : array
    {
        return array_values(array: array_filter(
                                       array   : $this->changeRequests,
                                       callback: static fn (TenantSecurityChangeRequest $tenantSecurityChangeRequest) : bool => $tenantSecurityChangeRequest->tenantSlug === trim(string: $tenantSlug),
                                   ));
    }
}
