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
    ) {
    }

    #[Override]
    public function get(): PooledConnection
    {
        while ( $connection = array_shift($this->connections) ) {
            // Check idle timeout before reusing
            if ($this->isIdleTimedOut($connection)) {
                $connection->close();
                $this->createdCount--;

                continue;
            }

            if ($this->validateConnection(pooledConnection: $connection)) {
                return $connection;
            }

            $connection->close();
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
        foreach ($this->connections as $connection) {
            $connection->close();
        }

        $this->connections = [];
        $this->createdCount = 0;
    }

    #[Override]
    public function stats(): PoolStats
    {
        return new PoolStats(
            totalConnections : $this->createdCount,
            activeConnections: $this->createdCount - count($this->connections),
            idleConnections  : count($this->connections),
            waitingRequests  : 0,
            averageWaitTimeMs: 0.0,
        );
    }

    public function warmup(int $count): void
    {
        for ($i = 0; $i < min($count, $this->minConnections); $i++) {
            $this->createdCount++;
            $this->release(pooledConnection: $this->createConnection());
        }
    }

    /**
     * Remove idle connections that have exceeded the timeout.
     * Returns the number of stale connections removed.
     */
    public function pruneIdle() : int
    {
        $removed = 0;
        $kept    = [];

        foreach ($this->connections as $connection) {
            if ($this->isIdleTimedOut($connection)) {
                $connection->close();
                $this->createdCount--;
                $removed++;
            } else {
                $kept[] = $connection;
            }
        }

        $this->connections = $kept;

        return $removed;
    }

    #[Override]
    public function release(PooledConnection $pooledConnection): void
    {
        if (! $this->validateConnection(pooledConnection: $pooledConnection)) {
            $pooledConnection->close();
            $this->createdCount--;

            return;
        }

        // Check idle timeout - stale connections should not be returned
        if ($this->isIdleTimedOut($pooledConnection)) {
            $pooledConnection->close();
            $this->createdCount--;

            return;
        }

        if (count($this->connections) >= $this->maxConnections / 2) {
            $pooledConnection->close();
            $this->createdCount--;

            return;
        }

        // Reset connection state before returning to pool
        $pooledConnection->reset();

        $this->connections[] = $pooledConnection;
    }

    /**
     * Check if a connection has exceeded the idle timeout.
     */
    protected function isIdleTimedOut(PooledConnection $connection) : bool
    {
        if ($this->idleTimeoutMs <= 0) {
            return false;
        }

        $idleTimeMs = (int) ((microtime(true) - $connection->getLastUsedAt()) * 1000);

        return $idleTimeMs > $this->idleTimeoutMs;
    }
}
