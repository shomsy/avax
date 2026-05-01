<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use Closure;
use InvalidArgumentException;
use Override;

final class LazyConnectionPool implements ConnectionPoolInterface
{
    private ?ConnectionPoolInterface $connectionPool = null;

    public function __construct(
        private readonly ?Closure $factory = null,
    ) {
        if (! $this->factory instanceof Closure) {
            throw new InvalidArgumentException(message: 'Pool factory is required.');
        }
    }

    #[Override]
    public function destroy(): void
    {
        $this->connectionPool?->destroy();
        $this->connectionPool = null;
    }

    #[Override]
    public function stats(): PoolStats
    {
        return $this->connectionPool?->stats() ?? new PoolStats(
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

    #[Override]
    public function get(): PooledConnection
    {
        return $this->pool()->get();
    }

    private function pool(): ConnectionPoolInterface
    {
        if (! $this->connectionPool instanceof ConnectionPoolInterface) {
            $pool = ($this->factory)();

            if (! $pool instanceof ConnectionPoolInterface) {
                throw new InvalidArgumentException(message: 'Pool factory must return a ConnectionPoolInterface.');
            }

            $this->connectionPool = $pool;
        }

        return $this->connectionPool;
    }

    #[Override]
    public function release(PooledConnection $pooledConnection): void
    {
        $this->pool()->release(connection: $pooledConnection);
    }
}
