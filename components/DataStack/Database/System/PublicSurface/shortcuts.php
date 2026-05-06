<?php

declare(strict_types=1);

/**
 * Database shortcuts for global access.
 */

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;

if (! function_exists('connection')) {
    /**
     * Retrieves a PDO database connection.
     *
     *
     * @throws RuntimeException
     */
    function connection(?string $connectionName = null): PDO
    {
        /** @var Connections $connections */
        $connections = app(Connections::class);

        if (! $connections instanceof Connections) {
            throw new RuntimeException('Database connection service is not registered in DI container.');
        }

        return $connections->pdo($connectionName);
    }
}
