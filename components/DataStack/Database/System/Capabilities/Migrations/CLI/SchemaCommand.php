<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\CLI;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\SchemaBuilder;
use Closure;

final class SchemaCommand
{
    public function create(string $table, Closure $callback): bool
    {
        $schemaBuilder = new SchemaBuilder();
        $callback($schemaBuilder);

        $schemaBuilder->createTable($table);

        return true;
    }

    public function drop(): bool
    {
        return true;
    }

    public function table(string $table, Closure $callback): bool
    {
        $schemaBuilder = new SchemaBuilder();
        $callback($schemaBuilder);

        return true;
    }
}
