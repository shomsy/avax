<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\SeedDatabase;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use RuntimeException;

/**
 * Base Seeder class.
 *
 * -- intent: provide a foundation for populating tables with sample or initial data.
 */
abstract class Seeder
{
    protected ?QueryBuilder $builder = null;

    /**
     * Seed the given seeder class.
     */
    public function call(string $class): void
    {
        basename(path: $class);
        echo sprintf('[36mSeeding:[0m %s%s', $class, PHP_EOL);
        new $class()->withBuilder(builder: $this->builder())->run();
    }

    /**
     * Run the database seeds.
     */
    abstract public function run(): void;

    public function withBuilder(QueryBuilder $queryBuilder): static
    {
        $this->builder = $queryBuilder;

        return $this;
    }

    protected function builder(): QueryBuilder
    {
        if (! $this->builder instanceof QueryBuilder) {
            throw new RuntimeException(message: 'Seeder requires an injected QueryBuilder before it can run.');
        }

        return $this->builder;
    }

    /**
     * Get a query builder instance for a table.
     */
    protected function command(string $table): QueryBuilder
    {
        return $this->builder()->from(table: $table);
    }
}
