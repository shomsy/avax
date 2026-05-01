<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR;

use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\ComparisonOperator;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\CTENode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\JoinNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\QueryNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\WhereNode;

final readonly class IRBuilder
{
    private QueryNode $queryNode;

    public function __construct(QueryNode $query = null)
    {
        $this->queryNode = $query ?? new QueryNode();
    }

    public static function query(): self
    {
        return new self();
    }

    public function select(string ...$columns): self
    {
        $this->queryNode->select(...$columns);

        return $this;
    }

    public function from(string $table, string $alias = null) : self
    {
        $this->queryNode->from(table: $table, alias: $alias);

        return $this;
    }

    public function join(string $table, string $type = null, WhereNode $on = null, string $alias = null) : self
    {
        $type ??= 'inner';
        $this->queryNode->join(join: new JoinNode(type: $type, table: $table, alias: $alias, on: $on));

        return $this;
    }

    public function where(
        string $column,
        ComparisonOperator $comparisonOperator,
        mixed $value = null,
        string $boolean = 'AND',
    ): self {
        $this->queryNode->where(where: new WhereNode(
            column  : $column,
            operator: $comparisonOperator,
            value   : $value,
            boolean : $boolean,
        ));

        return $this;
    }

    public function groupBy(string ...$columns): self
    {
        $this->queryNode->groupBy(...$columns);

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->queryNode->orderBy(column: $column, direction: $direction);

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->queryNode->limit(limit: $limit);

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->queryNode->offset(offset: $offset);

        return $this;
    }

    public function distinct(): self
    {
        $this->queryNode->distinct();

        return $this;
    }

    public function withCTE(CTENode $cteNode): self
    {
        $this->queryNode->withCTE(cte: $cteNode);

        return $this;
    }

    public function build(): QueryNode
    {
        return $this->queryNode;
    }
}
