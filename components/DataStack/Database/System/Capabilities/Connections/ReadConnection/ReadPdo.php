<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection;

use PDO;
use Throwable;

/**
 * Reads the raw PDO handle for one resolved database connection.
 */
final readonly class ReadPdo
{
    public function __construct(private ReadConnection $readConnection) {}

    /**
     * @throws Throwable
     */
    public function for(string|null $connectionName = null) : PDO
    {
        return $this->readConnection->connection(name: $connectionName)->getConnection();
    }
}
