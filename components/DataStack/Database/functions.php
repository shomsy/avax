<?php

declare(strict_types=1);

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;

if (! function_exists(function: 'connection')) {
    /**
     * Retrieves a PDO database connection.
     *
     * @param string|null $connectionName The name of the database connection to retrieve. Defaults to null for the default connection.
     *
     * @return PDO The PDO database connection instance.
     *
     * @throws RuntimeException If the database connection service is not available in the dependency injection container.
     * @throws Throwable
     */
    function connection(string $connectionName = null) : PDO
    {
        /** @var Connections $connections */
        $connections = app(abstract: Connections::class);

        if (! $connections instanceof Connections) {
            throw new RuntimeException(message: 'Database connection service is not registered in DI container.');
        }

        return $connections->pdo(name: $connectionName);
    }
}
