<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\PublicSurface;

use Avax\Components\Database\System\Capabilities\Connections\DatabaseConnection;

interface DatabaseInterface
{
    public function connection(string $name = 'default'): DatabaseConnection;

    public function transactions(): bool;

    public function schema(): SchemaBuilder;
}