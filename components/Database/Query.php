<?php

declare(strict_types=1);

namespace Avax\Components\Database;

use Avax\Components\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\Database\System\Capabilities\Query\Query as QueryCapability;
use Avax\Components\Database\System\Capabilities\Query\ValueObjects\Expression;
use ReflectionException;
use Throwable;

final readonly class Query
{
    public function __construct(
        private QueryCapability $query,
        private string|null     $connectionName = null
    ) {}

    public function on(string|null $connectionName = null) : self
    {
        return new self(query: $this->query, connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function builder(string|null $connectionName = null) : QueryBuilder
    {
        return $this->query->builder(connectionName: $connectionName ?? $this->connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function table(string $table, string|null $connectionName = null) : QueryBuilder
    {
        return $this->from(table: $table, connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function from(string $table, string|null $connectionName = null) : QueryBuilder
    {
        return $this->query->from(table: $table, connectionName: $connectionName ?? $this->connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function raw(string $value, string|null $connectionName = null) : Expression
    {
        return $this->query->raw(value: $value, connectionName: $connectionName ?? $this->connectionName);
    }
}
