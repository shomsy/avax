<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Flows\RunDatabaseQuery;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\DatabaseConnection;

final class PrepareDatabaseQuery
{
    public function prepare(string $sql, array $bindings) : array
    {
        return ['sql' => $sql, 'bindings' => $bindings];
    }
}