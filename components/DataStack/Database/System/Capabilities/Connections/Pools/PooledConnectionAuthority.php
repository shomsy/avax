<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\ConnectionContracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PoolContracts\ConnectionPoolInterface;
use Override;
use PDO;

/**
 * The "Personal Assistant" for picking up and returning database connections.
 *
 * -- what is it?
 * This is a smart proxy. It represents a database connection, but it doesn't
 * actually hold one until you REALLY need to use it.
 *
 * -- how to imagine it:
 * Think of someone sitting in a library. Instead of holding a book the
 * whole time (and blocking others from reading it), they only go to the
 * shelf and grab the book the second they need to look up a word. Once
 * they leave the library, they automatically put the book back.
 *
 * -- why this exists:
 * 1. Laziness: It prevents "checking out" a connection from the pool if
 *    your code doesn't end up actually running a query. This keeps
 *    connections available for other parts of your app longer.
 * 2. Automatic Return: Just like `BorrowedConnection`, it ensures that
 *    once this object is no longer needed, the connection goes back
 *    to the pool automatically.
 *
 * -- mental models:
 * - "Authority": This object is the middleman between you and the
 *    actual connection pool.
 *
 * -- what "lazy loading" means:
 * It's like only turning on the lights when you enter the room. Instead
 * of opening a database connection as soon as the app starts, we wait
 * until the very last second (when you actually try to run a query).
 * This saves memory and prevents our app from holding onto resources
 * it isn't using yet.
 *
 * -- what "proxy" means:
 * It's a "Stand-in" or a "Body Double". You talk to this object as
 * if it were a real database connection, but behind the scenes, it's
 * just managing the real connection for you.
 */
final class PooledConnectionAuthority implements ConnectionPoolInterface, DatabaseConnection
{
    /** @var DatabaseConnection|null The actual tool we've grabbed from the library (null if we haven't needed it yet). */
    private DatabaseConnection|null $databaseConnection = null;

    /**
     * @param  ConnectionPoolInterface  $connectionPool  The "Library" we borrow from.
     */
    public function __construct(private readonly ConnectionPoolInterface $connectionPool)
    {
    }

    /**
     * Get the active PDO tool. If we haven't borrowed one yet, we grab it now.
     */
    #[Override]
    public function getConnection(): PDO
    {
        return $this->resolveBorrowed()->getConnection();
    }

    /**
     * The "Grab it now" logic (Lazy Loading).
     *
     * -- how it works:
     * We check if we already have a connection. If not, we ask the pool
     * for a fresh one. We only do this once.
     */
    private function resolveBorrowed(): DatabaseConnection
    {
        if (! $this->databaseConnection instanceof DatabaseConnection) {
            $this->databaseConnection = $this->connectionPool->acquire();
        }

        return $this->databaseConnection;
    }

    /**
     * Standard protocol to ask the pool for a NEW connection.
     *
     * @return DatabaseConnection A fresh tool from the shared resource.
     */
    #[Override]
    public function acquire(): DatabaseConnection
    {
        return $this->connectionPool->acquire();
    }

    /**
     * Check if the database is still responsive.
     *
     * @return bool True if we can still "talk" to the database.
     */
    #[Override]
    public function ping(): bool
    {
        return $this->resolveBorrowed()->ping();
    }

    /**
     * Get the technical nickname of this connection.
     */
    #[Override]
    public function getName(): string
    {
        return $this->connectionPool->getName();
    }

    /**
     * Start a new database transaction.
     */
    #[Override]
    public function beginTransaction() : bool
    {
        return $this->resolveBorrowed()->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    #[Override]
    public function commit() : bool
    {
        return $this->resolveBorrowed()->commit();
    }

    /**
     * Roll back the current transaction.
     */
    #[Override]
    public function rollBack() : bool
    {
        return $this->resolveBorrowed()->rollBack();
    }

    /**
     * Execute a raw SQL statement (no result set).
     */
    #[Override]
    public function exec(string $sql) : int|false
    {
        return $this->resolveBorrowed()->exec($sql);
    }

    /**
     * Hand a connection back to the shared library.
     */
    #[Override]
    public function release(DatabaseConnection $databaseConnection): void
    {
        $this->connectionPool->release(databaseConnection: $databaseConnection);
    }

    /**
     * The "Closing Time" cleanup.
     *
     * -- intent:
     * When this assistant is no longer needed, it "clears its desk".
     * By setting `borrowed` to null, we trigger its own cleanup logic
     * (the destructor), which returns the connection to the pool.
     */
    public function __destruct()
    {
        $this->databaseConnection = null;
    }
}
