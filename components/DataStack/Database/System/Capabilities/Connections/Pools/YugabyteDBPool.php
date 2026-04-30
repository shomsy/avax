<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use Override;

/**
 * YugabyteDB connection pool.
 *
 * YugabyteDB is PostgreSQL-compatible, so this pool uses similar configuration.
 *
 * @todo Implement real YugabyteDB driver integration (PDO with YB-specific connection params)
 */
class YugabyteDBPool extends BaseConnectionPool
{
    public function __construct(
        protected array $config = [],
        int $minConnections = 5,
        int $maxConnections = 20,
        int $connectionTimeoutMs = 10000,
        int $idleTimeoutMs = 300000,
    )
    {
        parent::__construct(
            minConnections     : $minConnections,
            maxConnections     : $maxConnections,
            connectionTimeoutMs: $connectionTimeoutMs,
            idleTimeoutMs      : $idleTimeoutMs,
        );
    }

    #[Override]
    protected function createConnection() : PooledConnection
    {
        // @todo Replace with real YugabyteDB connection (compatible with PostgreSQL wire protocol)
        return new ArrayPooledConnection(config: $this->config);
    }

    #[Override]
    protected function validateConnection(PooledConnection $pooledConnection) : bool
    {
        return $pooledConnection->isValid();
    }
}
