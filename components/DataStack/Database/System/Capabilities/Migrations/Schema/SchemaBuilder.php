<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema;

final class SchemaBuilder
{
    public function create(string $table): void
    {
        // Simple implementation for ToDo
        echo sprintf('Creating table: %s%s', $table, PHP_EOL);
    }

    public function drop(string $table): void
    {
        echo sprintf('Dropping table: %s%s', $table, PHP_EOL);
    }
}
