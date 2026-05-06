<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Throwable;

/**
 * Creates one database through the schema runtime.
 */
final readonly class CreateDatabase
{
    public function __construct(private QueryBuilder $queryBuilder)
    {
    }

    /**
     * @throws Throwable
     */
    public function named(string $name): void
    {
        $this->queryBuilder->createDatabase(name: $name);
    }
}
