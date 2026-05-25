<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Permissions;

use Avax\Components\Identity\Foundation\Values\Permission;
use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;

interface PermissionDirectory
{
    public function grant(PermissionGrant $grant): void;

    public function has(UserId $userId, Permission $permission, TenantId|null $tenantId = null): bool;
}
