<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections\Pools;

use RuntimeException;

/**
 * Connection pool interface.
 */
interface ConnectionPoolInterface
{
    public function get() : PooledConnection;

    public function release(PooledConnection $connection) : void;

    public function destroy() : void;

    public function stats() : PoolStats;
}

/**
 * Pooled connection wrapper.
 */
interface PooledConnection
{
    public function getResource() : object;

    public function isValid() : bool;

    public function getCreatedAt() : float;

    public function getLastUsedAt() : float;

    public function executeCount() : int;
}

/**
 * Abstract connection pool with lazy initialization.
 */
abstract class BaseConnectionPool implements ConnectionPoolInterface
{
    protected array $connections = [];

    protected int $createdCount = 0;

    public function __construct(
        protected int $minConnections = 5,
        protected int $maxConnections = 20,
        protected int $connectionTimeoutMs = 10000,
        protected int $idleTimeoutMs = 300000,
    ) {}

    public function get() : PooledConnection
    {
        while ( $conn = array_shift(array: $this->connections) ) {
            if ($this->validateConnection(connection: $conn)) {
                return $conn;
            }
            $this->createdCount--;
        }

        if ($this->createdCount < $this->maxConnections) {
            $this->createdCount++;

            return $this->createConnection();
        }

        throw PoolException::poolExhausted();
    }

    abstract protected function validateConnection(PooledConnection $connection) : bool;

    abstract protected function createConnection() : PooledConnection;

    public function destroy() : void
    {
        $this->connections = [];
    }

    public function stats() : PoolStats
    {
        return new PoolStats(
            totalConnections : $this->createdCount,
            activeConnections: $this->createdCount - count(value: $this->connections),
            idleConnections  : count(value: $this->connections),
            waitingRequests  : 0,
            averageWaitTimeMs: 0.0,
        );
    }

    public function warmup(int $count) : void
    {
        for ($i = 0; $i < min($count, $this->minConnections); $i++) {
            $this->release(connection: $this->createConnection());
        }
    }

    public function release(PooledConnection $connection) : void
    {
        if (! $this->validateConnection(connection: $connection)) {
            $this->createdCount--;

            return;
        }

        if (count(value: $this->connections) >= $this->maxConnections / 2) {
            $this->createdCount--;

            return;
        }

        $this->connections[] = $connection;
    }
}

/**
 * Pool statistics.
 */
final class PoolStats
{
    public function __construct(
        public readonly int   $totalConnections,
        public readonly int   $activeConnections,
        public readonly int   $idleConnections,
        public readonly int   $waitingRequests,
        public readonly float $averageWaitTimeMs,
    ) {}
}

/**
 * Pool exceptions.
 */
final class PoolException extends RuntimeException
{
    public static function poolExhausted() : self
    {
        return new self(message: 'Connection pool exhausted');
    }

    public static function invalidConnection() : self
    {
        return new self(message: 'Invalid connection');
    }

    public static function timeout(int $timeoutMs) : self
    {
        return new self(message: "Connection timeout after {$timeoutMs}ms");
    }
}
