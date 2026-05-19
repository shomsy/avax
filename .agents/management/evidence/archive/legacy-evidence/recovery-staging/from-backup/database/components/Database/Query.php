<?php

declare(strict_types=1);

namespace components\Database;

use components\Database\System\Capabilities\Query\Builder\QueryBuilder;
use components\Database\System\Capabilities\Query\Query as QueryCapability;
use components\Database\System\Capabilities\Query\ValueObjects\Expression;
use ReflectionException;
use Throwable;

final readonly class Query
{
    public function __construct(
        private QueryCapability $query,
        private ?string $connectionName = null
    ) {
    }

    public function on(?string $connectionName = null): self
    {
        return new self(query: $this->query, connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function builder(?string $connectionName = null): QueryBuilder
    {
        return $this->query->builder(connectionName: $connectionName ?? $this->connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function table(string $table, ?string $connectionName = null): QueryBuilder
    {
        return $this->from(table: $table, connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function from(string $table, ?string $connectionName = null): QueryBuilder
    {
        return $this->query->from(table: $table, connectionName: $connectionName ?? $this->connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function raw(string $value, ?string $connectionName = null): Expression
    {
        return $this->query->raw(value: $value, connectionName: $connectionName ?? $this->connectionName);
    }
}
