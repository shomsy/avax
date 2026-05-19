<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Flows\RunDatabaseQuery;

use Avax\Components\Database\System\Capabilities\Connections\DatabaseConnection;

final class RunDatabaseQuery
{
    public function execute(DatabaseConnection $connection, string $query, array $bindings = []) : QueryResult
    {
        return new QueryResult(rows: [], affected: 0);
    }
}
