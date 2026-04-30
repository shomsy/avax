<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent;

use function is_string;

/**
 * Fluent builder for constructing DataQuery instances.
 *
 * Provides a chainable interface for building complex queries.
 */
final class DataQueryBuilder
{
    private string|null $entityType = null;
    private array    $conditions = [];
    private array    $orderBy    = [];
    private int|null $limit      = null;
    private int|null $offset     = null;
    private array    $joins      = [];
    private array    $select     = ['*'];

    /**
     * Sets the entity type to query.
     *
     * @param class-string $entityType
     */
    public function from(string $entityType) : DataQueryBuilder
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * Sets the fields to select.
     *
     * @param array<string>|string $fields
     */
    public function select(array|string $fields = ['*']) : DataQueryBuilder
    {
        if (is_string($fields)) {
            $fields = [$fields];
        }

        $this->select = $fields;

        return $this;
    }

    /**
     * Adds a where condition.
     *
     * @param mixed $value
     */
    public function where(string $field, mixed $value, string $operator = '=') : DataQueryBuilder
    {
        $this->conditions[] = [
            'field'    => $field,
            'value'    => $value,
            'operator' => $operator,
        ];

        return $this;
    }

    /**
     * Adds an ORDER BY clause.
     */
    public function orderBy(string $field, string $direction = 'ASC') : DataQueryBuilder
    {
        $this->orderBy[$field] = strtoupper($direction);

        return $this;
    }

    /**
     * Sets the LIMIT clause.
     */
    public function limit(int $limit) : DataQueryBuilder
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Sets the OFFSET clause.
     */
    public function offset(int $offset) : DataQueryBuilder
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Adds a LEFT JOIN clause.
     */
    public function leftJoin(string $table, string $on) : DataQueryBuilder
    {
        return $this->join('LEFT', $table, $on);
    }

    /**
     * Adds a JOIN clause.
     *
     * @param array{type: string, table: string, on: string} $join
     */
    public function join(string $type, string $table, string $on) : DataQueryBuilder
    {
        $this->joins[] = [
            'type'  => $type,
            'table' => $table,
            'on'    => $on,
        ];

        return $this;
    }

    /**
     * Adds an INNER JOIN clause.
     */
    public function innerJoin(string $table, string $on) : DataQueryBuilder
    {
        return $this->join('INNER', $table, $on);
    }

    /**
     * Builds and returns the DataQuery instance.
     */
    public function build() : DataQuery
    {
        return new DataQuery(
            entityType: $this->entityType,
            conditions: $this->conditions,
            orderBy   : $this->orderBy,
            limit     : $this->limit,
            offset    : $this->offset,
            joins     : $this->joins,
            select    : $this->select,
        );
    }

    /**
     * Resets the builder to its initial state.
     */
    public function reset() : DataQueryBuilder
    {
        $this->entityType = null;
        $this->conditions = [];
        $this->orderBy    = [];
        $this->limit      = null;
        $this->offset     = null;
        $this->joins      = [];
        $this->select     = ['*'];

        return $this;
    }
}
