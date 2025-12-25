<?php

declare(strict_types=1);

namespace Avax\Database\Connection\Contracts;

use PDO;

/**
 * Defines the essential contract for any database driver or connection wrapper.
 *
 * -- intent: ensure a consistent API across different database technologies.
 */
interface DatabaseConnection
{
    /**
     * Retrieve the underlying technical connection instance (PDO).
     *
     * -- intent: provide access to the raw driver for low-level operations.
     *
     * @return PDO The active PHP driver instance
     */
    public function getConnection() : PDO;

    /**
     * Verify the health and responsiveness of the database connection.
     *
     * -- intent: check if the connection is still alive and ready for queries.
     *
     * @return bool True if the connection is active and responsive
     */
    public function ping() : bool;

    /**
     * Retrieve the logical name/label of the active connection.
     *
     * -- intent: identify the connection for telemetry, logging, and debugging.
     *
     * @return string
     */
    public function getName() : string;
}
