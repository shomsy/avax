<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Exceptions\ConnectionException;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\Contracts\ConnectionPoolInterface;
use Override;
use PDO;

/**
 * RAII wrapper for a pooled database connection that auto-releases on destruction.
 *
 * @see /docs/Foundation/Database/Concepts/Connections.md
 */
final class BorrowedConnection implements DatabaseConnection
{
    /** @var bool A flag to make sure we don't try to return the connection twice. */
    private bool $released = false;

    /**
     * @param  DatabaseConnection  $databaseConnection  The actual, physical connection to the database.
     * @param  ConnectionPoolInterface  $connectionPool  The "Library Manager" that knows how to put this connection back on
     *                                                   the shelf.
     */
    public function __construct(private readonly DatabaseConnection $databaseConnection, private readonly ConnectionPoolInterface $connectionPool)
    {
    }

    /**
     * Get the underlying PDO tool to run your queries.
     *
     * @return PDO The active technical tool for the database.
     *
     * @throws ConnectionException If the connection was lost or closed unexpectedly.
     */
    #[Override]
    public function getConnection(): PDO
    {
        return $this->databaseConnection->getConnection();
    }

    /**
     * Check if the database is still alive and talking to us.
     *
     * @return bool True if it responds, false if the line is dead.
     */
    #[Override]
    public function ping(): bool
    {
        return $this->databaseConnection->ping();
    }

    /**
     * Get the technical nickname of this connection (e.g., 'primary', 'read-only').
     */
    #[Override]
    public function getName(): string
    {
        return $this->databaseConnection->getName();
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
    public function release(): void
    {
        if (! $this->released) {
            $this->connectionPool->release(connection: $this);
            $this->released = true;
        }
    }

    /**
     * Internal tool for the pool manager to see the "Raw" connection.
     *
     * @return DatabaseConnection The physical connection instance without the wrapper.
     *
     * @internal You should never need to call this in your application code.
     */
    public function getOriginalConnection(): DatabaseConnection
    {
        return $this->databaseConnection;
    }
}
