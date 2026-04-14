<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\TransferOwnership;

final readonly class TransferTenantOwnershipData
{
    public function __construct(
        public string $tenantSlug,
        public int $newOwnerUserId
    ) {}
}
