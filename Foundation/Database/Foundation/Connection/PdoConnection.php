<?php

declare(strict_types=1);

namespace Avax\Database\Connection;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use PDO;
use Throwable;

/**
 * Standard implementation of DatabaseConnection using PHP's PDO extension.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Connections.md
 */
final readonly class PdoConnection implements DatabaseConnection
{
    /**
     * @param  string  $name  The nickname for this connection (e.g., 'primary').
     * @param  PDO  $pdo  The active technical engine already plugged into the DB.
     */
    public function __construct(private string $name, private PDO $pdo) {}

    /**
     * Get the actual technical engine (PDO) to run your SQL.
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Send a heartbeat query to verify connection health.
     */
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
    public function getName(): string
    {
        return $this->name;
    }
}
