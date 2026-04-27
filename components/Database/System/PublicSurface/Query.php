<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\PublicSurface;

use Avax\Components\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\Database\System\Capabilities\Query\Query as QueryCapability;
use Avax\Components\Database\System\Capabilities\Query\ValueObjects\Expression;

/**
 * Public surface for Querying operations.
 */
final readonly class Query
{
    public function __construct(
        private QueryCapability $query,
        private ?string         $connectionName = null
    ) {}

    public function on(?string $connectionName = null) : self
    {
        return new self($this->query, $connectionName);
    }

    public function builder(?string $connectionName = null) : QueryBuilder
    {
        return $this->query->builder($connectionName ?? $this->connectionName);
    }

    public function table(string $table, ?string $connectionName = null) : QueryBuilder
    {
        return $this->from($table, $connectionName);
    }

    public function from(string $table, ?string $connectionName = null) : QueryBuilder
    {
        return $this->query->from($table, $connectionName ?? $this->connectionName);
    }

    public function raw(string $value, ?string $connectionName = null) : Expression
    {
        return $this->query->raw($value, $connectionName ?? $this->connectionName);
    }
}
