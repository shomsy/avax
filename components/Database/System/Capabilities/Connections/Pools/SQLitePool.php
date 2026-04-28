<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Connections\Pools;

final class SQLitePool extends MySQLPool
{
    public function __construct(array $config, int $minConnections = 1, int $maxConnections = 5)
    {
        parent::__construct(
            config             : $config,
            minConnections     : $minConnections,
            maxConnections     : $maxConnections,
            connectionTimeoutMs: 5000,
            idleTimeoutMs      : 300000,
        );
    }
}
