<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Migrations\Schema;

use Avax\Components\Database\System\Capabilities\Query\Builder\QueryBuilder;
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
