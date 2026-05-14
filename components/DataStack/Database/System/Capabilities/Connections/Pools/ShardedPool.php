<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use Closure;
use InvalidArgumentException;
use RuntimeException;

final class ShardedPool
{
    /** @var array<int, ConnectionPoolInterface> */
    private array $pools = [];

    public function __construct(
        private readonly int $shardCount,
        private readonly Closure $poolFactory,
    ) {
        if ($shardCount < 1) {
            throw new InvalidArgumentException(message: 'Shard count must be >= 1');
        }
    }

    public function getForUserId(int $userId): ConnectionPoolInterface
    {
        return $this->getForKey(key: (string) $userId);
    }

    /**
 * @throws RuntimeException
 */
public function getForKey(string $key): ConnectionPoolInterface
    {
        $shardIndex = abs(num: crc32(string: $key)) % $this->shardCount;

        if (! isset($this->pools[$shardIndex])) {
            $pool = ($this->poolFactory)($shardIndex);
            if (! $pool instanceof ConnectionPoolInterface) {
                throw new RuntimeException(message: 'Shard pool factory must return a ConnectionPoolInterface.');
            }

            $this->pools[$shardIndex] = $pool;
        }

        return $this->pools[$shardIndex];
    }

    public function getForTenant(string $tenantId): ConnectionPoolInterface
    {
        return $this->getForKey(key: $tenantId);
    }

    public function closeAll(): void
    {
        foreach ($this->pools as $pool) {
            $pool->destroy();
        }

        $this->pools = [];
    }

    public function getShardCount(): int
    {
        return $this->shardCount;
    }

    public function getStats(): array
    {
        $totalStats = new PoolStats(
            totalConnections : 0,
            activeConnections: 0,
            idleConnections  : 0,
            waitingRequests  : 0,
            averageWaitTimeMs: 0.0,
        );

        foreach ($this->pools as $pool) {
            $stats = $pool->stats();
            $totalStats = new PoolStats(
                totalConnections : $totalStats->totalConnections + $stats->totalConnections,
                activeConnections: $totalStats->activeConnections + $stats->activeConnections,
                idleConnections  : $totalStats->idleConnections + $stats->idleConnections,
                waitingRequests  : $totalStats->waitingRequests + $stats->waitingRequests,
                averageWaitTimeMs: ($totalStats->averageWaitTimeMs + $stats->averageWaitTimeMs) / 2,
            );
        }

        return ['total' => $totalStats, 'shards' => array_map(callback: static fn (ConnectionPoolInterface $connectionPool): PoolStats => $connectionPool->stats(), array: $this->pools)];
    }
}
