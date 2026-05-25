<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Permissions;

use Avax\Components\Identity\Foundation\Values\Permission;
use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;

final readonly class PermissionGrant
{
    public function __construct(private UserId $userId, private Permission $permission, private TenantId|null $tenantId = null) {}

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function permission(): Permission
    {
        return $this->permission;
    }

    public function tenantId(): TenantId|null
    {
        return $this->tenantId;
    }
}
