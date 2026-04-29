<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

/**
 * MongoDB connection pool.
 *
 * @todo Implement real MongoDB driver integration (ext-mongodb / MongoDB\Driver\Manager)
 */
class MongoDBPool extends BaseConnectionPool
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
        // @todo Replace with real MongoDB Manager: new \MongoDB\Driver\Manager($this->config['dsn'] ?? 'mongodb://localhost')
        return new ArrayPooledConnection(config: $this->config);
    }

    protected function validateConnection(PooledConnection $connection) : bool
    {
        return $connection->isValid();
    }
}
