<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Flows\RunDatabaseTransaction;

use Avax\Components\Database\System\Capabilities\Connections\DatabaseConnection;

final class RunDatabaseTransaction
{
    public function execute(DatabaseConnection $connection, callable $work): mixed
    {
        return $work();
    }

    public function begin(): void
    {
    }

    public function commit(): void
    {
    }

    public function rollback(): void
    {
    }
}
