<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\CompileDataQuery;

use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQuery;
use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQueryPlan;
use InvalidArgumentException;

use function end;
use function explode;
use function is_array;
use function is_string;

/**
 * CompileDataQuery flow.
 *
 * Compiles a DataQuery into a DataQueryPlan.
 * Generates SQL query string and resolves bindings.
 */
final class CompileDataQuery
{
    /**
     * Compiles a DataQuery into a DataQueryPlan.
     */
    public function compile(DataQuery $query): DataQueryPlan
    {
        if ($query->entityType === null) {
            throw new InvalidArgumentException('Cannot compile query without entity type.');
        }

        $tableName = $this->extractTableName($query->entityType);
        $sql = $this->buildSql($query, $tableName);
        $bindings = $this->extractBindings($query);

        return new DataQueryPlan(
            sql     : $sql,
            bindings: $bindings,
        );
    }

    /**
     * Extracts a table name from an entity class name.
     *
     * @param  class-string  $entityType
     */
    private function extractTableName(string $entityType): string
    {
        $parts = explode('\\', $entityType);
        $className = end($parts);

        return $this->camelToSnake($className);
    }

    /**
     * Converts camelCase to snake_case.
     */
    private function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * Builds SQL from the query and table name.
     */
    private function buildSql(DataQuery $query, string $tableName): string
    {
        $select = implode(', ', $query->select);
        $sql = "SELECT {$select} FROM {$tableName}";

        // Add JOINs
        foreach ($query->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['on']}";
        }

        // Add WHERE conditions
        $conditions = $this->buildWhereClause($query->conditions);
        if ($conditions !== '') {
            $sql .= " WHERE {$conditions}";
        }

        // Add ORDER BY
        if ($query->orderBy !== []) {
            $orderByParts = [];
            foreach ($query->orderBy as $field => $direction) {
                $orderByParts[] = "{$field} {$direction}";
            }
            $sql .= ' ORDER BY '.implode(', ', $orderByParts);
        }

        // Add LIMIT
        if ($query->limit !== null) {
            $sql .= " LIMIT {$query->limit}";
        }

        // Add OFFSET
        if ($query->offset !== null) {
            $sql .= " OFFSET {$query->offset}";
        }

        return $sql;
    }

    /**
     * Builds the WHERE clause from conditions.
     *
     * @param  array<array{field: string, value: mixed, operator: string}|mixed>  $conditions
     */
    private function buildWhereClause(array $conditions): string
    {
        if ($conditions === []) {
            return '';
        }

        $parts = [];

        foreach ($conditions as $condition) {
            if (is_array($condition) && isset($condition['field'], $condition['operator'])) {
                $parts[] = "{$condition['field']} {$condition['operator']} ?";
            } elseif (is_string($condition)) {
                $parts[] = $condition;
            }
        }

        return implode(' AND ', $parts);
    }

    /**
     * Extracts parameter bindings from the query.
     *
     * @return array<int, mixed>
     */
    private function extractBindings(DataQuery $query): array
    {
        $bindings = [];

        foreach ($query->conditions as $condition) {
            if (is_array($condition) && isset($condition['value'])) {
                $bindings[] = $condition['value'];
            }
        }

        return $bindings;
    }
}
