<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

/**
 * ClickHouse connection pool.
 *
 * @todo Implement real ClickHouse driver integration (smi2/phpClickhouse or similar)
 */
class ClickHousePool extends BaseConnectionPool
{
    public function __construct(
        protected array $config = [],
        int             $minConnections = 5,
        int             $maxConnections = 20,
        int             $connectionTimeoutMs = 10000,
        int             $idleTimeoutMs = 300000,
    )
    {
        parent::__construct(
            minConnections     : $minConnections,
            maxConnections     : $maxConnections,
            connectionTimeoutMs: $connectionTimeoutMs,
            idleTimeoutMs      : $idleTimeoutMs,
        );
    }

    protected function createConnection() : PooledConnection
    {
        // @todo Replace with real ClickHouse connection
        return new ArrayPooledConnection(config: $this->config);
    }

    protected function validateConnection(PooledConnection $connection) : bool
    {
        return $connection->isValid();
    }
}
