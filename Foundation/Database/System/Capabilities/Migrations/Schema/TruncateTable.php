<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Migrations\Schema;

use Avax\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Throwable;

/**
 * Truncates one table through the schema runtime.
 */
final readonly class TruncateTable
{
    public function __construct(private QueryBuilder $builder) {}

    /**
     * @throws Throwable
     */
    public function named(string $table) : void
    {
        $this->builder->truncate(table: $table);
    }
}
