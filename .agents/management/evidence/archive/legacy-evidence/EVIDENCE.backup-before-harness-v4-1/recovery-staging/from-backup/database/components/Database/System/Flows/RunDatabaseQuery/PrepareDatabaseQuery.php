<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Flows\RunDatabaseQuery;

final class PrepareDatabaseQuery
{
    public function prepare(string $sql, array $bindings) : array
    {
        return ['sql' => $sql, 'bindings' => $bindings];
    }
}
