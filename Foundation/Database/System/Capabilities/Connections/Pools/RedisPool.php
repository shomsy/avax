<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections\Pools;

final class RedisPool extends MySQLPool
{
    public function __construct(array $config, int $minConnections = 3, int $maxConnections = 10)
    {
        parent::__construct(
            config             : $config,
            minConnections     : $minConnections,
            maxConnections     : $maxConnections,
            connectionTimeoutMs: 5000,
            idleTimeoutMs      : 600000,
        );
    }

    public function pipeline(callable $commands) : array
    {
        return (array) $commands();
    }

    public function transaction(callable $commands) : array
    {
        return (array) $commands();
    }
}
