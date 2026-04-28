<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Throwable;

/**
 * Drops one database through the schema runtime.
 */
final readonly class DropDatabase
{
    public function __construct(private QueryBuilder $builder) {}

    /**
     * @throws Throwable
     */
    public function named(string $name) : void
    {
        $this->builder->dropDatabase(name: $name);
    }
}
