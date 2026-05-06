<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Flows\RunDatabaseQuery;

final class RunDatabaseQuery
{
    public function execute(): QueryResult
    {
        return new QueryResult(rows: [], affected: 0);
    }
}
