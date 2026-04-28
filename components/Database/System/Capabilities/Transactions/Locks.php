<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Transactions;

use Avax\Components\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;

final class Locks
{
    public function __construct(private readonly DatabaseConnection $connection) {}

    public function namedLock(string $name, int $timeoutSeconds = 10) : bool
    {
        $statement = $this->connection->getConnection()->prepare(query: 'SELECT GET_LOCK(?, ?)');
        $statement->execute(params: [$name, $timeoutSeconds]);

        return (bool) $statement->fetchColumn();
    }

    public function releaseNamedLock(string $name) : bool
    {
        $statement = $this->connection->getConnection()->prepare(query: 'SELECT RELEASE_LOCK(?)');
        $statement->execute(params: [$name]);

        return (bool) $statement->fetchColumn();
    }
}
