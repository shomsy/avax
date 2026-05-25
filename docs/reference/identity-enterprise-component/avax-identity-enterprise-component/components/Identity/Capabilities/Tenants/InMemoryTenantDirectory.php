<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Tenants;

use Avax\Components\Identity\Foundation\State\ResettableIdentityState;
use Avax\Components\Identity\Foundation\Values\TenantId;
use Avax\Components\Identity\Foundation\Values\UserId;

final class InMemoryTenantDirectory implements TenantDirectory, ResettableIdentityState
{
    /** @var array<string, true> */
    private array $memberships = [];

    public function addMembership(TenantMembership $membership): void
    {
        $this->memberships[$this->key($membership->userId(), $membership->tenantId())] = true;
    }

    public function userBelongsToTenant(UserId $userId, TenantId $tenantId): bool
    {
        return isset($this->memberships[$this->key($userId, $tenantId)]);
    }

    public function reset(): void
    {
        $this->memberships = [];
    }

    private function key(UserId $userId, TenantId $tenantId): string
    {
        return $userId->toString().'|'.$tenantId->toString();
    }
}
