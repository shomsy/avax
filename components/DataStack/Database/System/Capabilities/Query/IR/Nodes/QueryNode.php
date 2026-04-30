<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

final class QueryNode
{
    private array $select = [];

    private FromNode|null $from = null;

    private array $joins = [];

    private array $wheres = [];

    private array $groups = [];

    private array $orders = [];

    private int|null $limit = null;

    private int|null $offset = null;

    private bool $distinct = false;

    private array $ctes = [];

    public function select(string ...$columns) : self
    {
        $this->select = $columns;

        return $this;
    }

    public function from(string $table, string $alias = null) : self
    {
        $this->from = new FromNode(table: $table, alias: $alias);

        return $this;
    }

    public function join(JoinNode $join) : self
    {
        $this->joins[] = $join;

        return $this;
    }

    public function where(WhereNode $where) : self
    {
        $this->wheres[] = $where;

        return $this;
    }

    public function groupBy(string ...$columns) : self
    {
        $this->groups = $columns;

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC') : self
    {
        $this->orders[] = new OrderByNode(column: $column, direction: $direction);

        return $this;
    }

    public function limit(int $limit) : self
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset) : self
    {
        $this->offset = $offset;

        return $this;
    }

    public function distinct() : self
    {
        $this->distinct = true;

        return $this;
    }

    public function withCTE(CTENode $cte) : self
    {
        $this->ctes[] = $cte;

        return $this;
    }

    public function getSelect() : array
    {
        return $this->select;
    }

    public function getFrom() : FromNode|null
    {
        return $this->from;
    }

    public function getJoins() : array
    {
        return $this->joins;
    }

    public function getWheres() : array
    {
        return $this->wheres;
    }

    public function getGroups() : array
    {
        return $this->groups;
    }

    public function getOrders() : array
    {
        return $this->orders;
    }

    public function getLimit() : int|null
    {
        return $this->limit;
    }

    public function getOffset() : int|null
    {
        return $this->offset;
    }

    public function isDistinct() : bool
    {
        return $this->distinct;
    }

    public function getCTEs() : array
    {
        return $this->ctes;
    }
}
