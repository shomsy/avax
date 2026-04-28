<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership;

final readonly class TransferTenantOwnershipData
{
    public function __construct(public string $tenantSlug, public int $newOwnerUserId) {}
}
