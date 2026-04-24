<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Transactions;

use Avax\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use InvalidArgumentException;

final class SavepointManager
{
    public function __construct(private readonly DatabaseConnection $connection) {}

    public function create(string $name) : void
    {
        $this->assertValidName(name: $name);
        $this->connection->getConnection()->exec(statement: "SAVEPOINT {$name}");
    }

    private function assertValidName(string $name) : void
    {
        if ($name === '' || preg_match(pattern: '/^[A-Za-z_][A-Za-z0-9_]{0,63}$/', subject: $name) !== 1) {
            throw new InvalidArgumentException(message: "Invalid savepoint name: {$name}");
        }
    }

    public function release(string $name) : void
    {
        $this->assertValidName(name: $name);
        $this->connection->getConnection()->exec(statement: "RELEASE SAVEPOINT {$name}");
    }

    public function rollbackTo(string $name) : void
    {
        $this->assertValidName(name: $name);
        $this->connection->getConnection()->exec(statement: "ROLLBACK TO SAVEPOINT {$name}");
    }
}
