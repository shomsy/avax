<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

abstract class BaseConnectionPool
{
    public function __construct(
        protected readonly int $minConnections = 5,
        protected readonly int $maxConnections = 20,
        protected readonly int $connectionTimeoutMs = 10000,
        protected readonly int $idleTimeoutMs = 300000,
    ) {}

    abstract protected function createConnection() : PooledConnection;

    abstract protected function validateConnection(PooledConnection $pooledConnection) : bool;
}
