<?php

declare(strict_types=1);

namespace Gemini\Database\Connection\Contracts;

use PDO;

interface ConnectionPoolInterface
{
    /**
     * Retrieves a database connection by name.
     *
     * @param string|null $connectionName The name of the database connection.
     *
     * @return PDO The active PDO connection.
     */
    public function getConnection(string|null $connectionName = null) : PDO;

    /**
     * Releases a connection back to the pool.
     *
     * @param PDO $pdo The PDO connection instance.
     */
    public function releaseConnection(PDO $pdo) : void;
}
