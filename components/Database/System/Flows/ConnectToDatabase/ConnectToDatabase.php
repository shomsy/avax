<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Flows\ConnectToDatabase;

use Avax\Components\Database\System\Capabilities\Connections\DatabaseConnection;

final class ConnectToDatabase
{
    public function connect(array $config): DatabaseConnection
    {
        return new DatabaseConnection(
            name: $config['name'] ?? 'default',
        );
    }
}