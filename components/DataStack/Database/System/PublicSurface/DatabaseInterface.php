<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\DatabaseConnection;

interface DatabaseInterface
{
    public function connection(string $name = 'default'): DatabaseConnection;

    public function transactions(): bool;

    public function schema(): SchemaBuilder;
}
