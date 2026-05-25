<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Tenants;

use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;

interface TenantDirectory
{
    public function addMembership(TenantMembership $membership): void;

    public function userBelongsToTenant(UserId $userId, TenantId $tenantId): bool;
}
