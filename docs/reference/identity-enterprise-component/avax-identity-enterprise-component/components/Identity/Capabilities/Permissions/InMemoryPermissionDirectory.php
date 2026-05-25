<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Permissions;

use Avax\Components\Identity\Foundation\State\ResettableIdentityState;
use Avax\Components\Identity\Foundation\Values\Permission;
use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;

final class InMemoryPermissionDirectory implements PermissionDirectory, ResettableIdentityState
{
    /** @var array<string, true> */
    private array $grants = [];

    public function grant(PermissionGrant $grant): void
    {
        $this->grants[$this->key($grant->userId(), $grant->permission(), $grant->tenantId())] = true;
    }

    public function has(UserId $userId, Permission $permission, TenantId|null $tenantId = null): bool
    {
        return isset($this->grants[$this->key($userId, $permission, $tenantId)])
            || isset($this->grants[$this->key($userId, $permission, null)]);
    }

    public function reset(): void
    {
        $this->grants = [];
    }

    private function key(UserId $userId, Permission $permission, TenantId|null $tenantId): string
    {
        return $userId->toString().'|'.($tenantId?->toString() ?? '*').'|'.$permission->key();
    }
}
