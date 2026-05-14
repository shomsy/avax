<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\ConnectionContracts\DatabaseConnection;
use InvalidArgumentException;

final readonly class Savepoints
{
    public function __construct(private DatabaseConnection $databaseConnection)
    {
    }

    public function create(string $name): void
    {
        $this->assertValidName(name: $name);
        $this->databaseConnection->getConnection()->exec(statement: 'SAVEPOINT '.$name);
    }

    private function assertValidName(string $name): void
    {
        if ($name === '' || preg_match(pattern: '/^[A-Za-z_]\w{0,63}$/', subject: $name) !== 1) {
            throw new InvalidArgumentException(message: 'Invalid savepoint name: '.$name);
        }
    }

    public function release(string $name): void
    {
        $this->assertValidName(name: $name);
        $this->databaseConnection->getConnection()->exec(statement: 'RELEASE SAVEPOINT '.$name);
    }

    public function rollbackTo(string $name): void
    {
        $this->assertValidName(name: $name);
        $this->databaseConnection->getConnection()->exec(statement: 'ROLLBACK TO SAVEPOINT '.$name);
    }
}
