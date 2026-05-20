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
use function preg_match;

/**
 * CompileDataQuery flow.
 *
 * Compiles a DataQuery into a DataQueryPlan.
 * Generates SQL query string and resolves bindings.
 */
final class CompileDataQuery
{
    /**
     * Allowed SQL join types.
     */
    private const ALLOWED_JOIN_TYPES = [
        'INNER' => 'INNER',
        'LEFT'  => 'LEFT',
        'LEFT OUTER' => 'LEFT OUTER',
        'RIGHT' => 'RIGHT',
        'RIGHT OUTER' => 'RIGHT OUTER',
        'CROSS' => 'CROSS',
        'JOIN'  => 'JOIN',
    ];

    /**
     * Allowed SQL comparison operators for conditions.
     */
    private const ALLOWED_OPERATORS = [
        '=' => '=', '!=' => '!=', '<>' => '<>', '<' => '<', '<=' => '<=',
        '>' => '>', '>=' => '>=', 'LIKE' => 'LIKE', 'NOT LIKE' => 'NOT LIKE',
        'IN' => 'IN', 'NOT IN' => 'NOT IN', 'IS' => 'IS', 'IS NOT' => 'IS NOT',
        'BETWEEN' => 'BETWEEN', 'NOT BETWEEN' => 'NOT BETWEEN',
    ];

    /**
     * Allowed ORDER BY directions.
     */
    private const ALLOWED_DIRECTIONS = ['ASC' => 'ASC', 'DESC' => 'DESC'];

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

        return $this->wrapIdentifier($this->camelToSnake($className));
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
        $selectParts = [];
        foreach ($dataQuery->select as $column) {
            $selectParts[] = $this->wrapIdentifier($column);
        }
        $select = implode(', ', $selectParts);
        $sql = sprintf('SELECT %s FROM %s', $select, $tableName);

        // Add JOINs
        foreach ($dataQuery->joins as $join) {
            $joinType = $this->validateJoinType($join['type']);
            $joinTable = $this->wrapIdentifier($join['table']);
            $joinOn = $this->validateJoinOnClause($join['on']);
            $sql .= sprintf(' %s JOIN %s ON %s', $joinType, $joinTable, $joinOn);
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
                $orderByParts[] = sprintf('%s %s', $this->wrapIdentifier($field), $this->validateDirection($direction));
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
                $field = $this->wrapIdentifier($condition['field']);
                $operator = $this->validateOperator($condition['operator']);
                $parts[] = sprintf('%s %s ?', $field, $operator);
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

    /**
     * Wraps a SQL identifier in double quotes after validation.
     *
     * Supports dotted identifiers (table.column) and wildcard (*).
     */
    private function wrapIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);

        if ($identifier === '*') {
            return '*';
        }

        // Handle dotted identifiers: table.column or alias.column
        if (str_contains($identifier, '.')) {
            $segments = explode('.', $identifier);
            $wrapped = [];
            foreach ($segments as $segment) {
                $this->validateIdentifierSegment($segment);
                $wrapped[] = '"'.str_replace('"', '""', $segment).'"';
            }
            return implode('.', $wrapped);
        }

        $this->validateIdentifierSegment($identifier);

        return '"'.str_replace('"', '""', $identifier).'"';
    }

    /**
     * Validates a single identifier segment against the strict allowlist.
     */
    private function validateIdentifierSegment(string $segment): void
    {
        if ($segment === '*') {
            return;
        }

        if (! preg_match('/^[a-zA-Z_]\w*$/', $segment)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid SQL identifier segment: "%s". Only alphanumeric characters and underscores are allowed.',
                $segment,
            ));
        }
    }

    /**
     * Validates and normalizes a JOIN type.
     */
    private function validateJoinType(string $type): string
    {
        $normalized = strtoupper(trim($type));

        if (! isset(self::ALLOWED_JOIN_TYPES[$normalized])) {
            throw new InvalidArgumentException(sprintf(
                'Invalid JOIN type: "%s". Allowed: %s.',
                $type,
                implode(', ', array_keys(self::ALLOWED_JOIN_TYPES)),
            ));
        }

        return self::ALLOWED_JOIN_TYPES[$normalized];
    }

    /**
     * Validates a JOIN ON clause, wrapping identifiers within it.
     *
     * Expected format: "table.column = other.column" or similar simple equality.
     */
    private function validateJoinOnClause(string $on): string
    {
        // The ON clause is a compound expression like "table.column = other.column"
        // We validate that it contains only safe identifier characters, operators, and spaces
        $normalized = trim($on);

        // Strict allowlist: only alphanumeric, underscore, dot, space, =, <, >, !, AND, OR
        if (! preg_match('/^[a-zA-Z_\s.=<>!]+$/', $normalized)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid JOIN ON clause: "%s". Contains disallowed characters.',
                $on,
            ));
        }

        // Wrap individual identifiers within the ON clause
        return $this->wrapOnClauseIdentifiers($normalized);
    }

    /**
     * Wraps identifiers within a JOIN ON clause expression.
     *
     * Splits on comparison operators and wraps each side as an identifier.
     */
    private function wrapOnClauseIdentifiers(string $clause): string
    {
        // Match patterns like "identifier = identifier" or "identifier.operator = identifier"
        if (preg_match('/^([a-zA-Z_]\w*(?:\.[a-zA-Z_]\w*)?)\s*([=<>!]+)\s*([a-zA-Z_]\w*(?:\.[a-zA-Z_]\w*)?)$/', $clause, $matches)) {
            $left = $this->wrapIdentifier($matches[1]);
            $operator = $matches[2];
            $right = $this->wrapIdentifier($matches[3]);

            return sprintf('%s %s %s', $left, $operator, $right);
        }

        // If it doesn't match the simple pattern, validate the whole thing as safe characters
        // This allows simple string clauses that passed the character allowlist above
        return $clause;
    }

    /**
     * Validates and normalizes an ORDER BY direction.
     */
    private function validateDirection(string $direction): string
    {
        $normalized = strtoupper(trim($direction));

        if (! isset(self::ALLOWED_DIRECTIONS[$normalized])) {
            throw new InvalidArgumentException(sprintf(
                'Invalid ORDER BY direction: "%s". Allowed: ASC, DESC.',
                $direction,
            ));
        }

        return self::ALLOWED_DIRECTIONS[$normalized];
    }

    /**
     * Validates and normalizes a comparison operator.
     */
    private function validateOperator(string $operator): string
    {
        $normalized = strtoupper(trim($operator));

        if (! isset(self::ALLOWED_OPERATORS[$normalized])) {
            throw new InvalidArgumentException(sprintf(
                'Invalid comparison operator: "%s".',
                $operator,
            ));
        }

        return self::ALLOWED_OPERATORS[$normalized];
    }
}
