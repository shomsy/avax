<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Migrations\SchemaOperations;

use Avax\Database\System\Capabilities\Querying\Builder\QueryBuilder;
use Throwable;

/**
 * Drops one table through the schema runtime.
 */
final readonly class DropTable
{
    public function __construct(private QueryBuilder $builder) {}

    /**
     * @throws Throwable
     */
    public function named(string $table) : void
    {
        $this->builder->dropIfExists(table: $table);
    }
}
