<?php

declare(strict_types=1);

namespace Avax\Database\Connection;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use PDO;
use Throwable;

/**
 * Concrete technician for the native PHP Data Object connection.
 *
 * -- intent: provide a domain-specific wrapper around the standard PDO engine.
 */
final readonly class PdoConnection implements DatabaseConnection
{
    /**
     * Constructor promoting the native driver instance.
     *
     * -- intent: encapsulate the physical driver within the component contract.
     *
     * @param string $name Technical identifier/label of the connection
     * @param PDO    $pdo  Active PHP driver instance
     */
    public function __construct(private string $name, private PDO $pdo) {}

    /**
     * Retrieve the internal PDO instance.
     *
     * -- intent: expose the driver for raw SQL execution.
     *
     * @return PDO
     */
    public function getConnection() : PDO
    {
        return $this->pdo;
    }

    /**
     * Perform a low-level heartbeat check on the connection.
     *
     * -- intent: verify the physical socket or server is still responsive.
     *
     * @return bool
     */
    public function ping() : bool
    {
        try {
            $this->pdo->query(query: "SELECT 1");

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Retrieve the logical name/label of the active connection.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
}
