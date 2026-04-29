<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

/**
 * Elasticsearch connection pool.
 *
 * @todo Implement real Elasticsearch driver integration (elasticsearch-php client)
 */
class ElasticsearchPool extends BaseConnectionPool
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
        // @todo Replace with real Elasticsearch client: \Elasticsearch\ClientBuilder::create()->setHosts(...)->build()
        return new ArrayPooledConnection(config: $this->config);
    }

    protected function validateConnection(PooledConnection $connection) : bool
    {
        return $connection->isValid();
    }
}
