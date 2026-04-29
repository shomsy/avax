<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

/**
 * CockroachDB connection pool.
 *
 * CockroachDB is PostgreSQL wire-protocol compatible.
 * This pool includes built-in retry logic for transaction contention errors.
 *
 * @todo Implement real CockroachDB driver integration (PDO with crdb-specific retry handling)
 */
class CockroachDBPool extends BaseConnectionPool
{
    private int $maxRetries = 3;

    public function __construct(
        protected array $config = [],
        int             $minConnections = 5,
        int             $maxConnections = 20,
        int             $connectionTimeoutMs = 10000,
        int             $idleTimeoutMs = 300000,
        int             $maxRetries = 3,
    )
    {
        parent::__construct(
            minConnections     : $minConnections,
            maxConnections     : $maxConnections,
            connectionTimeoutMs: $connectionTimeoutMs,
            idleTimeoutMs      : $idleTimeoutMs,
        );

        $this->maxRetries = $maxRetries;
    }

    protected function createConnection() : PooledConnection
    {
        // @todo Replace with real CockroachDB connection (PostgreSQL-compatible wire protocol)
        return new ArrayPooledConnection(config: $this->config);
    }

    protected function validateConnection(PooledConnection $connection) : bool
    {
        return $connection->isValid();
    }

    /**
     * Get a wrapped retryable pool for handling CockroachDB transaction retries.
     */
    public function withRetry(?int $maxRetries = null) : RetryablePool
    {
        return new RetryablePool(
            pool      : $this,
            maxRetries: $maxRetries ?? $this->maxRetries
        );
    }

    /**
     * Get the configured max retry count.
     */
    public function maxRetries() : int
    {
        return $this->maxRetries;
    }
}
