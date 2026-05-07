<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Runtime\Tenant\TransferOwnership;

final readonly class TransferTenantOwnershipData
{
    public function __construct(public string $tenantSlug, public int $newOwnerUserId) {}
}
