<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Query\IR;

use components\Database\System\Capabilities\Query\IR\Nodes\ComparisonOperator;
use components\Database\System\Capabilities\Query\IR\Nodes\CTENode;
use components\Database\System\Capabilities\Query\IR\Nodes\JoinNode;
use components\Database\System\Capabilities\Query\IR\Nodes\QueryNode;
use components\Database\System\Capabilities\Query\IR\Nodes\WhereNode;

final class IRBuilder
{
    private QueryNode $query;

    public function __construct(?QueryNode $query = null)
    {
        $this->query = $query ?? new QueryNode();
    }

    public static function query(): self
    {
        return new self();
    }

    public function select(string ...$columns): self
    {
        $this->query->select(...$columns);

        return $this;
    }

    public function from(string $table, ?string $alias = null): self
    {
        $this->query->from(table: $table, alias: $alias);

        return $this;
    }

    public function join(string $table, ?string $type = null, ?WhereNode $on = null, ?string $alias = null): self
    {
        $type ??= 'inner';
        $this->query->join(join: new JoinNode(type: $type, table: $table, alias: $alias, on: $on));

        return $this;
    }

    public function where(
        string $column,
        ComparisonOperator $operator,
        mixed $value = null,
        string $boolean = 'AND',
    ): self {
        $this->query->where(where: new WhereNode(
            column  : $column,
            operator: $operator,
            value   : $value,
            boolean : $boolean,
        ));

        return $this;
    }

    public function groupBy(string ...$columns): self
    {
        $this->query->groupBy(...$columns);

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->query->orderBy(column: $column, direction: $direction);

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->query->limit(limit: $limit);

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->query->offset(offset: $offset);

        return $this;
    }

    public function distinct(): self
    {
        $this->query->distinct();

        return $this;
    }

    public function withCTE(CTENode $cte): self
    {
        $this->query->withCTE(cte: $cte);

        return $this;
    }

    public function build(): QueryNode
    {
        return $this->query;
    }
}
