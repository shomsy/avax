<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\TransferOwnership;

final readonly class TransferTenantOwnershipData
{
    public int    $newOwnerUserId;
    public string $tenantSlug;

    public function __construct(
        string $tenantSlug,
        int    $newOwnerUserId
    )
    {
        $this->tenantSlug     = $tenantSlug;
        $this->newOwnerUserId = $newOwnerUserId;
    }
}
