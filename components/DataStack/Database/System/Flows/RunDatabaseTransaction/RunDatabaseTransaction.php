<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Flows\RunDatabaseTransaction;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\DatabaseConnection;

final class RunDatabaseTransaction
{
    public function execute(DatabaseConnection $databaseConnection, callable $work) : mixed
    {
        return $work();
    }
}
