<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Permissions;

use Avax\Components\Identity\Foundation\Values\Permission;
use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;

final readonly class ExplicitPermissionDecision implements DecidePermission
{
    public function __construct(private PermissionDirectory $permissions) {}

    public function isAllowed(UserId $userId, Permission $permission, TenantId|null $tenantId = null): bool
    {
        return $this->permissions->has($userId, $permission, $tenantId);
    }
}
