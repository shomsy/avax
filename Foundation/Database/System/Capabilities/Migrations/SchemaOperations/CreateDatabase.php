<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Migrations\SchemaOperations;

use Avax\Database\System\Capabilities\Querying\Builder\QueryBuilder;
use Throwable;

/**
 * Creates one database through the schema runtime.
 */
final readonly class CreateDatabase
{
    public function __construct(private QueryBuilder $builder) {}

    /**
     * @throws Throwable
     */
    public function named(string $name) : void
    {
        $this->builder->createDatabase(name: $name);
    }
}
