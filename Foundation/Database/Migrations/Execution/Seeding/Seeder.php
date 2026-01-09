<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Seeding;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;

/**
 * Base Seeder class.
 *
 * -- intent: provide a foundation for populating tables with sample or initial data.
 */
abstract class Seeder
{
    /**
     * Seed the given seeder class.
     */
    public function call(string $class) : void
    {
        basename(path: $class);
        echo "\033[36mSeeding:\033[0m {$class}\n";
        (new $class())->run();
    }

    /**
     * Run the database seeds.
     */
    abstract public function run() : void;

    /**
     * Get a query builder instance for a table.
     */
    protected function command(string $table) : QueryBuilder
    {
        return app(abstract: QueryBuilder::class)->from(table: $table);
    }
}
