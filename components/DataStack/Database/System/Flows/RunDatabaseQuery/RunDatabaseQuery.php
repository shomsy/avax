<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Flows\RunDatabaseQuery;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\DatabaseConnection;

final class RunDatabaseQuery
{
    public function execute() : QueryResult
    {
        return new QueryResult(rows: [], affected: 0);
    }
}
