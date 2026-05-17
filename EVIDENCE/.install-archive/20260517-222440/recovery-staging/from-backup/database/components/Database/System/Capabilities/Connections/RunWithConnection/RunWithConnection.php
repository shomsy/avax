<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections\RunWithConnection;

use Avax\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Database\System\Capabilities\Connections\Pools\Contracts\ConnectionPoolInterface;
use Avax\Database\System\Capabilities\Connections\ReadConnection\ReadConnection;
use Throwable;

/**
 * Runs a callback with one resolved connection, optionally borrowing from a pool.
 */
final readonly class RunWithConnection
{
    public function __construct(private ReadConnection $readConnection)
    {
    }

    /**
     * @param  callable(DatabaseConnection): mixed  $callback
     *
     * @throws Throwable
     */
    public function run(callable $callback, ?string $connectionName = null): mixed
    {
        return $callback($this->readConnection->connection(name: $connectionName));
    }

    /**
     * @param  callable(DatabaseConnection): mixed  $callback
     *
     * @throws Throwable
     */
    public function pool(callable $callback, ?string $connectionName = null): mixed
    {
        $connection = $this->readConnection->connection(name: $connectionName);

        if (! $connection instanceof ConnectionPoolInterface) {
            return $callback($connection);
        }

        $borrowed = $connection->acquire();

        try {
            return $callback($borrowed);
        } finally {
            $connection->release(connection: $borrowed);
        }
    }
}
