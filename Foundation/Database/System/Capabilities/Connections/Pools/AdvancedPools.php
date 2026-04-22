<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections\Pools;

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Multi-tenant connection pool - isolates tenant connections.
 */
final class MultiTenantPool
{
    private array $pools = [];

    private string $tenantKey = 'tenant_id';

    public function __construct(
        private Closure $poolFactory,
        private int     $maxTenants = 100,
    ) {}

    public function getForTenant(string $tenantId) : ConnectionPoolInterface
    {
        if (! isset($this->pools[$tenantId])) {
            if (count($this->pools) >= $this->maxTenants) {
                throw new RuntimeException("Max tenants reached: {$this->maxTenants}");
            }

            $this->pools[$tenantId] = ($this->poolFactory)($tenantId);
        }

        return $this->pools[$tenantId];
    }

    public function releaseForTenant(string $tenantId, PooledConnection $connection) : void
    {
        if (isset($this->pools[$tenantId])) {
            $this->pools[$tenantId]->release($connection);
        }
    }

    public function closeTenant(string $tenantId) : void
    {
        if (isset($this->pools[$tenantId])) {
            $this->pools[$tenantId]->destroy();
            unset($this->pools[$tenantId]);
        }
    }

    public function closeAll() : void
    {
        foreach ($this->pools as $pool) {
            $pool->destroy();
        }
        $this->pools = [];
    }

    public function getStats() : array
    {
        $stats = [];
        foreach ($this->pools as $tenantId => $pool) {
            $stats[$tenantId] = $pool->stats();
        }

        return $stats;
    }

    public function getTenantCount() : int
    {
        return count($this->pools);
    }
}

/**
 * Sharded pool - distributes connections across shards.
 */
final class ShardedPool
{
    private array $pools = [];

    public function __construct(
        private int     $shardCount,
        private Closure $poolFactory,
    )
    {
        if ($shardCount < 1) {
            throw new InvalidArgumentException('Shard count must be >= 1');
        }
    }

    public function getForUserId(int $userId) : ConnectionPoolInterface
    {
        return $this->getForKey((string) $userId);
    }

    public function getForKey(string $key) : ConnectionPoolInterface
    {
        $shardIndex = abs(crc32($key)) % $this->shardCount;

        if (! isset($this->pools[$shardIndex])) {
            $this->pools[$shardIndex] = ($this->poolFactory)($shardIndex);
        }

        return $this->pools[$shardIndex];
    }

    public function getForTenant(string $tenantId) : ConnectionPoolInterface
    {
        return $this->getForKey($tenantId);
    }

    public function closeAll() : void
    {
        foreach ($this->pools as $pool) {
            $pool->destroy();
        }
        $this->pools = [];
    }

    public function getShardCount() : int
    {
        return $this->shardCount;
    }

    public function getStats() : array
    {
        $totalStats = new PoolStats(
            totalConnections : 0,
            activeConnections: 0,
            idleConnections  : 0,
            waitingRequests  : 0,
            averageWaitTimeMs: 0.0,
        );

        foreach ($this->pools as $pool) {
            $stats      = $pool->stats();
            $totalStats = new PoolStats(
                totalConnections : $totalStats->totalConnections + $stats->totalConnections,
                activeConnections: $totalStats->activeConnections + $stats->activeConnections,
                idleConnections  : $totalStats->idleConnections + $stats->idleConnections,
                waitingRequests  : $totalStats->waitingRequests + $stats->waitingRequests,
                averageWaitTimeMs: ($totalStats->averageWaitTimeMs + $stats->averageWaitTimeMs) / 2,
            );
        }

        return ['total' => $totalStats, 'shards' => array_map(fn ($p) => $p->stats(), $this->pools)];
    }
}

/**
 * Read/Write split pool.
 */
final class ReadWritePool
{
    public function __construct(
        private ConnectionPoolInterface $readPool,
        private ConnectionPoolInterface $writePool,
        private bool                    $enableReadWriteSplit = true,
    ) {}

    public function getRead() : PooledConnection
    {
        if (! $this->enableReadWriteSplit) {
            return $this->writePool->get();
        }

        return $this->readPool->get();
    }

    public function getWrite() : PooledConnection
    {
        return $this->writePool->get();
    }

    public function releaseRead(PooledConnection $connection) : void
    {
        $this->readPool->release($connection);
    }

    public function releaseWrite(PooledConnection $connection) : void
    {
        $this->writePool->release($connection);
    }

    public function isReadWriteSplitEnabled() : bool
    {
        return $this->enableReadWriteSplit;
    }

    public function enableReadWriteSplit(bool $enabled = true) : void
    {
        $this->enableReadWriteSplit = $enabled;
    }
}
