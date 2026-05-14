<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\ConnectionContracts\DatabaseConnection;
use Override;
use PDO;
use Throwable;

/**
 * Standard implementation of DatabaseConnection using PHP's PDO extension.
 *
 * @see /docs/Foundation/Database/Concepts/Connections.md
 */
final readonly class PdoConnection implements DatabaseConnection
{
    /**
     * @param  string  $name  The nickname for this connection (e.g., 'primary').
     * @param  PDO  $pdo  The active technical engine already plugged into the DB.
     */
    public function __construct(private string $name, private PDO $pdo)
    {
    }

    /**
     * Get the actual technical engine (PDO) to run your SQL.
     */
    #[Override]
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Send a heartbeat query to verify connection health.
     */
    #[Override]
    public function ping(): bool
    {
        try {
            $this->pdo->query(query: 'SELECT 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Get the nickname assigned to this connection.
     */
    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Start a new database transaction.
     */
    #[Override]
    public function beginTransaction() : bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    #[Override]
    public function commit() : bool
    {
        return $this->pdo->commit();
    }

    /**
     * Roll back the current transaction.
     */
    #[Override]
    public function rollBack() : bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Execute a raw SQL statement (no result set).
     */
    #[Override]
    public function exec(string $sql) : int|false
    {
        return $this->pdo->exec($sql);
    }
}
