<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Flows\ConnectToDatabase;

use Avax\Components\Database\System\Capabilities\Connections\DatabaseConnection;

final class ResolveConnectionConfiguration
{
    public function resolve(string $name): array
    {
        return ['name' => $name];
    }
}