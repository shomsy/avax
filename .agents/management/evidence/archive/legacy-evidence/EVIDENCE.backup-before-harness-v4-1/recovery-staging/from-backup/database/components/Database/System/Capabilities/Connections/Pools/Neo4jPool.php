<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Connections\Pools;

final class Neo4jPool extends MySQLPool
{
    public function executeInTransaction(callable $operations) : mixed
    {
        return $operations();
    }
}
