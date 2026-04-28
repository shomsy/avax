<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use PDO;
use Throwable;

/**
 * Standard implementation of DatabaseConnection using PHP's PDO extension.
 *
 * @see /docs/Foundation/Database/Concepts/Connections.md
 */
final readonly class PdoConnection implements DatabaseConnection
{
    private PDO    $pdo;
    private string $name;

    /**
     * @param string $name The nickname for this connection (e.g., 'primary').
     * @param PDO    $pdo  The active technical engine already plugged into the DB.
     */
    public function __construct(string $name, PDO $pdo)
    {
        $this->name = $name;
        $this->pdo  = $pdo;
    }

    /**
     * Get the actual technical engine (PDO) to run your SQL.
     */
    public function getConnection() : PDO
    {
        return $this->pdo;
    }

    /**
     * Send a heartbeat query to verify connection health.
     */
    public function ping() : bool
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
    public function getName() : string
    {
        return $this->name;
    }
}
