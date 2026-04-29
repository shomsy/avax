<?php
declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema;

final class SchemaBuilder
{
    public function create(string $table, callable $callback): void
    {
        // Simple implementation for ToDo
        echo "Creating table: $table\n";
    }

    public function drop(string $table): void
    {
        echo "Dropping table: $table\n";
    }
}
