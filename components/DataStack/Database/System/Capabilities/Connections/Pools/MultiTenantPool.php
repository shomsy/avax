<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use Closure;
use RuntimeException;

final class MultiTenantPool
{
    /** @var array<string, ConnectionPoolInterface> */
    private array $pools = [];

    public function __construct(
        private readonly Closure $poolFactory,
        private readonly int $maxTenants = 100,
    ) {}

    public function getForTenant(string $tenantId): ConnectionPoolInterface
    {
        if (! isset($this->pools[$tenantId])) {
            if (count(value: $this->pools) >= $this->maxTenants) {
                throw new RuntimeException(message: 'Max tenants reached: ' . $this->maxTenants);
            }

            $pool = ($this->poolFactory)($tenantId);
            if (! $pool instanceof ConnectionPoolInterface) {
                throw new RuntimeException(message: 'Tenant pool factory must return a ConnectionPoolInterface.');
            }

            $this->pools[$tenantId] = $pool;
        }

        return $this->pools[$tenantId];
    }

    public function releaseForTenant(string $tenantId, PooledConnection $pooledConnection): void
    {
        if (isset($this->pools[$tenantId])) {
            $this->pools[$tenantId]->release(connection: $pooledConnection);
        }
    }

    public function closeTenant(string $tenantId): void
    {
        if (! isset($this->pools[$tenantId])) {
            return;
        }

        $this->pools[$tenantId]->destroy();
        unset($this->pools[$tenantId]);
    }

    public function closeAll(): void
    {
        foreach ($this->pools as $pool) {
            $pool->destroy();
        }

        $this->pools = [];
    }

    public function getStats(): array
    {
        $stats = [];

        foreach ($this->pools as $tenantId => $pool) {
            $stats[$tenantId] = $pool->stats();
        }

        return $stats;
    }

    public function getTenantCount(): int
    {
        return count(value: $this->pools);
    }
}
