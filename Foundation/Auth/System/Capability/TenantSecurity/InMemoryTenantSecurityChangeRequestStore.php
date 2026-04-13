<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantSecurity;

final class InMemoryTenantSecurityChangeRequestStore implements TenantSecurityChangeRequestStoreInterface
{
    /** @var array<string, TenantSecurityChangeRequest> */
    private array $changeRequests = [];

    public function save(TenantSecurityChangeRequest $changeRequest) : void
    {
        $this->changeRequests[$changeRequest->changeId] = $changeRequest;
    }

    public function find(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->changeRequests[$changeId] ?? null;
    }

    public function allForTenant(string $tenantSlug) : array
    {
        return array_values(array_filter(
            $this->changeRequests,
            static fn (TenantSecurityChangeRequest $changeRequest) : bool => $changeRequest->tenantSlug === trim($tenantSlug)
        ));
    }
}
