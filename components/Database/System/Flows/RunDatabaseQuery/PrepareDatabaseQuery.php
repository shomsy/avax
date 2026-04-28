<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Flows\RunDatabaseQuery;

use Avax\Components\Database\System\Capabilities\Connections\DatabaseConnection;

final class PrepareDatabaseQuery
{
    public function prepare(string $sql, array $bindings): array
    {
        return ['sql' => $sql, 'bindings' => $bindings];
    }
}