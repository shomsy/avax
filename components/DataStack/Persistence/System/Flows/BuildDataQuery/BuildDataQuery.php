<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\BuildDataQuery;

use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQuery;
use InvalidArgumentException;
use function class_exists;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

/**
 * BuildDataQuery flow.
 *
 * Takes query intent and produces a validated DataQuery.
 * Validates conditions and resolves entity types.
 */
final class BuildDataQuery
{
    /**
     * @param  array<string, class-string>  $entityRegistry
     */
    public function __construct(private array $entityRegistry = [])
    {
    }

    /**
     * Builds a DataQuery from the given parameters.
     *
     * @param  class-string|string|null  $entityType
     * @param  array<string, mixed>  $conditions
     * @param  array<string, string>  $orderBy
     * @param  array<string>  $select
     * @param  array<array{type: string, table: string, on: string}>  $joins
     *
     * @throws InvalidArgumentException
     */
    public function build(string|null $entityType = null,
        array $conditions = [],
                          array       $orderBy = [], int|null $limit = null, int|null $offset = null,
        array $joins = [],
        array $select = ['*'],
    ): DataQuery {
        $resolvedEntityType = $this->resolveEntityType($entityType);

        return new DataQuery(
            entityType: $resolvedEntityType,
            conditions: $this->validateConditions($conditions),
            orderBy   : $this->validateOrderBy($orderBy),
            limit     : $this->validateLimit($limit),
            offset    : $this->validateOffset($offset),
            joins     : $joins,
            select    : $select,
        );
    }

    /**
     * Resolves an entity type alias to a class name.
     *
     * @param  class-string|string|null  $entityType
     * @return class-string|null
     *
     * @throws InvalidArgumentException
     */
    private function resolveEntityType(string|null $entityType) : string|null
    {
        if ($entityType === null) {
            return null;
        }

        if (isset($this->entityRegistry[$entityType])) {
            return $this->entityRegistry[$entityType];
        }

        if (class_exists($entityType)) {
            return $entityType;
        }

        throw new InvalidArgumentException(
            sprintf('Unknown entity type: "%s"', $entityType),
        );
    }

    /**
     * Validates query conditions.
     *
     * @param  array<string, mixed>  $conditions
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    private function validateConditions(array $conditions): array
    {
        foreach ($conditions as $key => $value) {
            if (is_array($value) && isset($value['field'], $value['value'], $value['operator'])) {
                continue;
            }

            if (is_string($key) && ! is_array($value)) {
                continue;
            }

            if (is_int($key) && is_array($value)) {
                continue;
            }

            throw new InvalidArgumentException(
                sprintf('Invalid condition format for key "%s"', $key),
            );
        }

        return $conditions;
    }

    /**
     * Validates ORDER BY clauses.
     *
     * @param  array<string, string>  $orderBy
     * @return array<string, string>
     *
     * @throws InvalidArgumentException
     */
    private function validateOrderBy(array $orderBy): array
    {
        $validDirections = ['ASC', 'DESC'];

        foreach ($orderBy as $field => $direction) {
            $direction = strtoupper($direction);
            if (! in_array($direction, $validDirections, true)) {
                throw new InvalidArgumentException(
                    sprintf('Invalid order direction "%s" for field "%s". Must be ASC or DESC.', $direction, $field),
                );
            }

            $orderBy[$field] = $direction;
        }

        return $orderBy;
    }

    /**
     * Validates LIMIT value.
     *
     * @throws InvalidArgumentException
     */
    private function validateLimit(int|null $limit) : int|null
    {
        if ($limit !== null && $limit < 0) {
            throw new InvalidArgumentException('Limit must be a non-negative integer.');
        }

        return $limit;
    }

    /**
     * Validates OFFSET value.
     *
     * @throws InvalidArgumentException
     */
    private function validateOffset(int|null $offset) : int|null
    {
        if ($offset !== null && $offset < 0) {
            throw new InvalidArgumentException('Offset must be a non-negative integer.');
        }

        return $offset;
    }

    /**
     * Builds a DataQuery from an existing DataQuery instance, validating it.
     *
     * @throws InvalidArgumentException
     */
    public function buildFrom(DataQuery $dataQuery): DataQuery
    {
        return new DataQuery(
            entityType: $this->resolveEntityType($dataQuery->entityType),
            conditions: $this->validateConditions($dataQuery->conditions),
            orderBy   : $this->validateOrderBy($dataQuery->orderBy),
            limit     : $this->validateLimit($dataQuery->limit),
            offset    : $this->validateOffset($dataQuery->offset),
            joins     : $dataQuery->joins,
            select    : $dataQuery->select,
        );
    }

    /**
     * Registers an entity type alias.
     *
     * @param  class-string  $className
     */
    public function registerEntity(string $alias, string $className): void
    {
        $this->entityRegistry[$alias] = $className;
    }
}
