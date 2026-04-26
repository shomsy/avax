<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Migrations\SeedDatabase;

use components\Database\System\Capabilities\Query\Builder\QueryBuilder;
use RuntimeException;

/**
 * Base Seeder class.
 *
 * -- intent: provide a foundation for populating tables with sample or initial data.
 */
abstract class Seeder
{
    protected QueryBuilder|null $builder = null;

    /**
     * Seed the given seeder class.
     */
    public function call(string $class) : void
    {
        basename(path: $class);
        echo "\033[36mSeeding:\033[0m {$class}\n";
        (new $class)->withBuilder(builder: $this->builder())->run();
    }

    /**
     * Run the database seeds.
     */
    abstract public function run() : void;

    public function withBuilder(QueryBuilder $builder) : static
    {
        $this->builder = $builder;

        return $this;
    }

    protected function builder() : QueryBuilder
    {
        if ($this->builder === null) {
            throw new RuntimeException(message: 'Seeder requires an injected QueryBuilder before it can run.');
        }

        return $this->builder;
    }

    /**
     * Get a query builder instance for a table.
     */
    protected function command(string $table) : QueryBuilder
    {
        return $this->builder()->from(table: $table);
    }
}
