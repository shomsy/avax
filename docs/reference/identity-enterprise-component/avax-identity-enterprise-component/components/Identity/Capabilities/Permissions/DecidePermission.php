<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Permissions;

use Avax\Components\Identity\Foundation\Values\Permission;
use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;

interface DecidePermission
{
    public function isAllowed(UserId $userId, Permission $permission, TenantId|null $tenantId = null): bool;
}
