<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections\Pools;

final class Neo4jPool extends MySQLPool
{
    public function executeInTransaction(callable $operations) : mixed
    {
        return $operations();
    }
}
