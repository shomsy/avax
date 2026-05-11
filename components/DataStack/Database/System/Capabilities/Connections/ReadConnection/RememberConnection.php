<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ConnectionPool;

/**
 * Owns in-memory remembering of direct connections and pools.
 */
final class RememberConnection
{
    /**
     * @param  array<string, DatabaseConnection>  $connections
     */
    public function read(array $connections, string $name) : DatabaseConnection|null
    {
        return $connections[$name] ?? null;
    }

    /**
     * @param  array<string, DatabaseConnection>  $connections
     */
    public function remember(array &$connections, string $name, DatabaseConnection $databaseConnection): DatabaseConnection
    {
        $connections[$name] = $databaseConnection;

        return $databaseConnection;
    }

    /**
     * @param  array<string, ConnectionPool>  $pools
     */
    public function readPool(array $pools, string $name) : ConnectionPool|null
    {
        return $pools[$name] ?? null;
    }

    /**
     * @param  array<string, ConnectionPool>  $pools
     */
    public function rememberPool(array &$pools, string $name, ConnectionPool $connectionPool): ConnectionPool
    {
        $pools[$name] = $connectionPool;

        return $connectionPool;
    }
}
