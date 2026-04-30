<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent;

/**
 * Immutable value object representing a query intent.
 *
 * Encapsulates all aspects of a data query without executing it.
 */
final readonly class DataQuery
{
    /**
     * @param class-string|null     $entityType
     * @param array<string, mixed>  $conditions
     * @param array<string, string> $orderBy
     * @param array<string>         $select
     * @param array<array{type: string, table: string, on: string}> $joins
     */
    public function __construct(
        public string|null $entityType = null,
        public array    $conditions = [],
        public array    $orderBy = [],
        public int|null $limit = null,
        public int|null $offset = null,
        public array    $joins = [],
        public array    $select = ['*'],
    ) {}

    /**
     * Returns a new instance with the added condition.
     *
     * @return DataQuery
     */
    public function withCondition(string $field, mixed $value, string $operator = '=') : DataQuery
    {
        $conditions   = $this->conditions;
        $conditions[] = [
            'field'    => $field,
            'value'    => $value,
            'operator' => $operator,
        ];

        return new DataQuery(
            entityType: $this->entityType,
            conditions: $conditions,
            orderBy   : $this->orderBy,
            limit     : $this->limit,
            offset    : $this->offset,
            joins     : $this->joins,
            select    : $this->select,
        );
    }

    /**
     * Returns a new instance with the specified order by clause.
     *
     * @return DataQuery
     */
    public function withOrderBy(string $field, string $direction = 'ASC') : DataQuery
    {
        $orderBy         = $this->orderBy;
        $orderBy[$field] = strtoupper($direction);

        return new DataQuery(
            entityType: $this->entityType,
            conditions: $this->conditions,
            orderBy   : $orderBy,
            limit     : $this->limit,
            offset    : $this->offset,
            joins     : $this->joins,
            select    : $this->select,
        );
    }

    /**
     * Returns a new instance with the specified limit.
     *
     * @return DataQuery
     */
    public function withLimit(int $limit) : DataQuery
    {
        return new DataQuery(
            entityType: $this->entityType,
            conditions: $this->conditions,
            orderBy   : $this->orderBy,
            limit     : $limit,
            offset    : $this->offset,
            joins     : $this->joins,
            select    : $this->select,
        );
    }

    /**
     * Returns a new instance with the specified offset.
     *
     * @return DataQuery
     */
    public function withOffset(int $offset) : DataQuery
    {
        return new DataQuery(
            entityType: $this->entityType,
            conditions: $this->conditions,
            orderBy   : $this->orderBy,
            limit     : $this->limit,
            offset    : $offset,
            joins     : $this->joins,
            select    : $this->select,
        );
    }

    /**
     * Returns a new instance with the specified joins.
     *
     * @param array<array{type: string, table: string, on: string}> $joins
     *
     * @return DataQuery
     */
    public function withJoins(array $joins) : DataQuery
    {
        return new DataQuery(
            entityType: $this->entityType,
            conditions: $this->conditions,
            orderBy   : $this->orderBy,
            limit     : $this->limit,
            offset    : $this->offset,
            joins     : [...$this->joins, ...$joins],
            select    : $this->select,
        );
    }

    /**
     * Returns a new instance with the specified select fields.
     *
     * @param array<string> $select
     *
     * @return DataQuery
     */
    public function withSelect(array $select) : DataQuery
    {
        return new DataQuery(
            entityType: $this->entityType,
            conditions: $this->conditions,
            orderBy   : $this->orderBy,
            limit     : $this->limit,
            offset    : $this->offset,
            joins     : $this->joins,
            select    : $select,
        );
    }

    /**
     * Returns a new instance with the specified entity type.
     *
     * @param class-string $entityType
     *
     * @return DataQuery
     */
    public function withEntityType(string $entityType) : DataQuery
    {
        return new DataQuery(
            entityType: $entityType,
            conditions: $this->conditions,
            orderBy   : $this->orderBy,
            limit     : $this->limit,
            offset    : $this->offset,
            joins     : $this->joins,
            select    : $this->select,
        );
    }
}
