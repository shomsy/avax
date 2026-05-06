<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections\ReadConnection;

use Avax\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Database\System\Capabilities\Connections\Pools\ConnectionPool;

/**
 * Owns in-memory remembering of direct connections and pools.
 */
final class RememberConnection
{
    /**
     * @param  array<string, DatabaseConnection>  $connections
     */
    public function read(array $connections, string $name): ?DatabaseConnection
    {
        return $connections[$name] ?? null;
    }

    /**
     * @param  array<string, DatabaseConnection>  $connections
     */
    public function remember(array &$connections, string $name, DatabaseConnection $connection): DatabaseConnection
    {
        $connections[$name] = $connection;

        return $connection;
    }

    /**
     * @param  array<string, ConnectionPool>  $pools
     */
    public function readPool(array $pools, string $name): ?ConnectionPool
    {
        return $pools[$name] ?? null;
    }

    /**
     * @param  array<string, ConnectionPool>  $pools
     */
    public function rememberPool(array &$pools, string $name, ConnectionPool $pool): ConnectionPool
    {
        $pools[$name] = $pool;

        return $pool;
    }
}
