<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Connections\Pools;

use Closure;
use InvalidArgumentException;

final class LazyConnectionPool implements ConnectionPoolInterface
{
    private ?ConnectionPoolInterface $pool = null;

    public function __construct(
        private readonly array $config,
        private readonly int $maxConnections = 20,
        private readonly ?Closure $factory = null,
    ) {
        if ($this->factory === null) {
            throw new InvalidArgumentException(message: 'Pool factory is required.');
        }
    }

    public function destroy(): void
    {
        $this->pool?->destroy();
        $this->pool = null;
    }

    public function stats(): PoolStats
    {
        return $this->pool?->stats() ?? new PoolStats(
            totalConnections : 0,
            activeConnections: 0,
            idleConnections  : 0,
            waitingRequests  : 0,
            averageWaitTimeMs: 0.0,
        );
    }

    public function warmup(int $count = 1): void
    {
        if ($count < 1) {
            return;
        }

        for ($index = 0; $index < $count; $index++) {
            $connection = $this->get();
            $this->release(connection: $connection);
        }
    }

    public function get(): PooledConnection
    {
        return $this->pool()->get();
    }

    private function pool(): ConnectionPoolInterface
    {
        if ($this->pool === null) {
            $pool = ($this->factory)();

            if (! $pool instanceof ConnectionPoolInterface) {
                throw new InvalidArgumentException(message: 'Pool factory must return a ConnectionPoolInterface.');
            }

            $this->pool = $pool;
        }

        return $this->pool;
    }

    public function release(PooledConnection $connection): void
    {
        $this->pool()->release(connection: $connection);
    }
}
