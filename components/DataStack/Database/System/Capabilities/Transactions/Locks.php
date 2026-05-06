<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;

final readonly class Locks
{
    public function __construct(private DatabaseConnection $databaseConnection)
    {
    }

    public function namedLock(string $name, int $timeoutSeconds = 10): bool
    {
        $statement = $this->databaseConnection->getConnection()->prepare(query: 'SELECT GET_LOCK(?, ?)');
        $statement->execute(params: [$name, $timeoutSeconds]);

        return (bool) $statement->fetchColumn();
    }

    public function releaseNamedLock(string $name): bool
    {
        $statement = $this->databaseConnection->getConnection()->prepare(query: 'SELECT RELEASE_LOCK(?)');
        $statement->execute(params: [$name]);

        return (bool) $statement->fetchColumn();
    }
}
