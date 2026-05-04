<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use Override;

abstract class ConnectionPool implements ConnectionPoolInterface
{
    /** @var list<PooledConnection> */
    protected array $connections = [];

    protected int $createdCount = 0;

    public function __construct(
        protected int $minConnections = 5,
        protected int $maxConnections = 20,
        protected int $connectionTimeoutMs = 10000,
        protected int $idleTimeoutMs = 300000,
    ) {}

    #[Override]
    public function get(): PooledConnection
    {
        while ($connection = array_shift(array: $this->connections)) {
            if ($this->validateConnection(connection: $connection)) {
                return $connection;
            }

            $this->createdCount--;
        }

        if ($this->createdCount < $this->maxConnections) {
            $this->createdCount++;

            return $this->createConnection();
        }

        throw PoolException::poolExhausted();
    }

    abstract protected function validateConnection(PooledConnection $pooledConnection): bool;

    abstract protected function createConnection(): PooledConnection;

    #[Override]
    public function destroy(): void
    {
        $this->connections = [];
        $this->createdCount = 0;
    }

    #[Override]
    public function stats(): PoolStats
    {
        return new PoolStats(
            totalConnections : $this->createdCount,
            activeConnections: $this->createdCount - count(value: $this->connections),
            idleConnections  : count(value: $this->connections),
            waitingRequests  : 0,
            averageWaitTimeMs: 0.0,
        );
    }

    public function warmup(int $count): void
    {
        for ($i = 0; $i < min($count, $this->minConnections); $i++) {
            $this->release(connection: $this->createConnection());
        }
    }

    #[Override]
    public function release(PooledConnection $pooledConnection): void
    {
        if (! $this->validateConnection(connection: $pooledConnection)) {
            $this->createdCount--;

            return;
        }

        if (count(value: $this->connections) >= $this->maxConnections / 2) {
            $this->createdCount--;

            return;
        }

        $this->connections[] = $pooledConnection;
    }
}
