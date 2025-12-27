<?php

declare(strict_types=1);

namespace Avax\Database\Foundation\Connection\Pool;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Foundation\Connection\Pool\Contracts\ConnectionPoolInterface;
use Avax\Database\Foundation\Connection\Pool\Exceptions\ConnectionException;
use PDO;

/**
 * RAII wrapper for a pooled database connection that auto-releases on destruction.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Connections.md
 */
final class BorrowedConnection implements DatabaseConnection
{
    /** @var bool A flag to make sure we don't try to return the connection twice. */
    private bool $released = false;

    /**
     * @param DatabaseConnection      $connection The actual, physical connection to the database.
     * @param ConnectionPoolInterface $pool       The "Library Manager" that knows how to put this connection back on
     *                                            the shelf.
     */
    public function __construct(
        private readonly DatabaseConnection      $connection,
        private readonly ConnectionPoolInterface $pool
    ) {}

    /**
     * Get the underlying PDO tool to run your queries.
     *
     * @return PDO The active technical tool for the database.
     * @throws ConnectionException If the connection was lost or closed unexpectedly.
     */
    public function getConnection() : PDO
    {
        return $this->connection->getConnection();
    }

    /**
     * Check if the database is still alive and talking to us.
     *
     * @return bool True if it responds, false if the line is dead.
     */
    public function ping() : bool
    {
        return $this->connection->ping();
    }

    /**
     * Get the technical nickname of this connection (e.g., 'primary', 'read-only').
     */
    public function getName() : string
    {
        return $this->connection->getName();
    }

    /**
     * Auto-release the connection when this wrapper is destroyed.
     */
    public function __destruct()
    {
        $this->release();
    }

    /**
     * Manually return the connection to the pool early.
     */
    public function release() : void
    {
        if (! $this->released) {
            $this->pool->release(connection: $this);
            $this->released = true;
        }
    }

    /**
     * Internal tool for the pool manager to see the "Raw" connection.
     *
     * @return DatabaseConnection The physical connection instance without the wrapper.
     * @internal You should never need to call this in your application code.
     */
    public function getOriginalConnection() : DatabaseConnection
    {
        return $this->connection;
    }
}
