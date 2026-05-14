<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\CLI;

use Avax\Components\DataStack\Database\System\PublicSurface\Database;
use Closure;
use Throwable;

final class SchemaCommand
{
    public function __construct(private Database $database) {}

    /**
     * @throws Throwable
     */
    public function create(string $table, Closure $callback): bool
    {
        $this->database->schema()->create(table: $table, callback: $callback);

        return true;
    }

    /**
     * @throws Throwable
     */
    public function drop(string $table) : bool
    {
        $this->database->schema()->drop(table: $table);

        return true;
    }

    /**
     * @throws Throwable
     */
    public function table(string $table, Closure $callback): bool
    {
        $this->database->schema()->table(table: $table, callback: $callback);

        return true;
    }
}
