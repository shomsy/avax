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
    public function compile(DataQuery $dataQuery): DataQueryPlan
    {
        if ($dataQuery->entityType === null) {
            throw new InvalidArgumentException('Cannot compile query without entity type.');
        }

        $tableName = $this->extractTableName($dataQuery->entityType);
        $sql = $this->buildSql($dataQuery, $tableName);
        $bindings = $this->extractBindings($dataQuery);

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
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * Builds SQL from the query and table name.
     */
    private function buildSql(DataQuery $dataQuery, string $tableName): string
    {
        $select = implode(', ', $dataQuery->select);
        $sql = sprintf('SELECT %s FROM %s', $select, $tableName);

        // Add JOINs
        foreach ($dataQuery->joins as $join) {
            $sql .= sprintf(' %s JOIN %s ON %s', $join['type'], $join['table'], $join['on']);
        }

        // Add WHERE conditions
        $conditions = $this->buildWhereClause($dataQuery->conditions);
        if ($conditions !== '') {
            $sql .= ' WHERE '.$conditions;
        }

        // Add ORDER BY
        if ($dataQuery->orderBy !== []) {
            $orderByParts = [];
            foreach ($dataQuery->orderBy as $field => $direction) {
                $orderByParts[] = sprintf('%s %s', $field, $direction);
            }

            $sql .= ' ORDER BY '.implode(', ', $orderByParts);
        }

        // Add LIMIT
        if ($dataQuery->limit !== null) {
            $sql .= ' LIMIT '.$dataQuery->limit;
        }

        // Add OFFSET
        if ($dataQuery->offset !== null) {
            $sql .= ' OFFSET '.$dataQuery->offset;
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
                $parts[] = sprintf('%s %s ?', $condition['field'], $condition['operator']);
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
    private function extractBindings(DataQuery $dataQuery): array
    {
        $bindings = [];

        foreach ($dataQuery->conditions as $condition) {
            if (is_array($condition) && isset($condition['value'])) {
                $bindings[] = $condition['value'];
            }
        }

        return $bindings;
    }
}
