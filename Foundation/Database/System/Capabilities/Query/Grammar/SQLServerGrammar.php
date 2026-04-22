<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Grammar;

use Avax\Database\System\Capabilities\Query\State\QueryState;
use Avax\Database\System\Capabilities\Query\ValueObjects\Expression;
use Override;
use RuntimeException;

/**
 * SQL Server Grammar with support for:
 * - MERGE (upsert)
 * - OUTPUT clause
 * - Window functions
 * - CTEs (WITH ... )
 * - PIVOT/UNPIVOT
 * - hierarchyid
 */
final class SQLServerGrammar extends BaseGrammar
{
    #[Override]
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update) : string
    {
        $table = $this->wrap($state->from);
        $rows  = $this->normalizeInsertRows($state->values);

        if (empty($rows)) {
            throw new RuntimeException(message: 'INSERT compilation requires at least one row of values.');
        }

        $columns = implode(', ', array_map(fn ($c) => $this->wrap($c), array_keys($rows[0])));

        $valueGroups = [];
        foreach ($rows as $row) {
            $placeholders = [];
            foreach ($row as $value) {
                $placeholders[] = $value instanceof Expression ? $value->getValue() : '?';
            }
            $valueGroups[] = '(' . implode(', ', $placeholders) . ')';
        }

        $conflictColumns = implode(', ', array_map(
            fn ($col) => $this->wrap($col),
            $uniqueBy
        ));

        $updates = [];
        foreach ($update as $column) {
            $updates[] = $this->wrap($column) . ' = source.' . $this->wrap($column);
        }
        $updateSet = implode(', ', $updates);

        $valuesSql = 'VALUES ' . implode(', ', $valueGroups);

        $sql = "MERGE {$table} AS target\n";
        $sql .= "USING (SELECT {$columns} {$valuesSql}) AS source ({$columns})\n";
        $sql .= "ON target.{$conflictColumns} = source.{$conflictColumns}\n";
        $sql .= "WHEN MATCHED THEN UPDATE SET {$updateSet}\n";
        $sql .= "WHEN NOT MATCHED THEN INSERT ({$columns}) VALUES ({$columns});";

        return $sql;
    }

    #[Override]
    public function wrap(mixed $value) : string
    {
        if ($value instanceof Expression) {
            return $value->getValue();
        }

        $value = (string) $value;

        if ($value === '*' || str_contains($value, '(')) {
            return $value;
        }

        if (str_contains($value, '.')) {
            return implode('.', array_map(
                fn ($segment) => $this->wrapSegment($segment),
                explode('.', $value)
            ));
        }

        return $this->wrapSegment($value);
    }

    protected function wrapSegment(string $segment) : string
    {
        if ($segment === '*' || $segment === '') {
            return $segment;
        }

        return '[' . str_replace(']', ']]', $segment) . ']';
    }

    private function normalizeInsertRows(array $values) : array
    {
        if ($values === []) {
            return [];
        }

        if (array_is_list($values) && is_array($values[0] ?? null)) {
            return $values;
        }

        return [$values];
    }

    #[Override]
    public function compileRandomOrder() : string
    {
        return 'NEWID()';
    }

    #[Override]
    public function compileTruncate(string $table) : string
    {
        return 'TRUNCATE TABLE ' . $this->wrap($table);
    }

    #[Override]
    public function compileDropIfExists(string $table) : string
    {
        return "IF OBJECT_ID('" . $table . "') IS NOT NULL DROP TABLE " . $this->wrap($table);
    }

    #[Override]
    public function compileCreateDatabase(string $name) : string
    {
        return 'CREATE DATABASE ' . $this->wrap($name);
    }

    #[Override]
    public function compileDropDatabase(string $name) : string
    {
        return 'DROP DATABASE ' . $this->wrap($name);
    }

    public function compileOutput(array $columns) : string
    {
        if (empty($columns)) {
            return '';
        }

        $cols = implode(', ', array_map(fn ($col) => $this->wrap($col), $columns));

        return 'OUTPUT ' . $cols;
    }

    public function compilePivot(string $column, string $pivotColumn, array $pivotValues) : string
    {
        $pivotcols = implode(', ', array_map(fn ($val) => "['{$val}']", $pivotValues));

        return "PIVOT ({$column} FOR {$pivotColumn} IN ({$pivotcols}))";
    }

    public function compileUnpivot(string $column, array $unpivotColumns) : string
    {
        $unpivcols = implode(', ', array_map(fn ($col) => $this->wrap($col), $unpivotColumns));

        return "UNPIVOT ({$column} IN ({$unpivcols}))";
    }

    public function compileWindowFunction(string $function, string $partitionBy = '', string $orderBy = '') : string
    {
        $sql = $function . '(';

        if ($partitionBy !== '') {
            $partitionColumns = implode(', ', array_map(
                fn ($col) => $this->wrap($col),
                explode(',', $partitionBy)
            ));
            $sql              .= 'PARTITION BY ' . $partitionColumns;
        }

        if ($orderBy !== '') {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        $sql .= ')';

        return $sql;
    }

    public function compileWithRecursive(string $name, string $columns, string $initialQuery, string $recursiveQuery) : string
    {
        return "WITH {$name} AS (SELECT {$columns} FROM ({$initialQuery}) AS initial UNION ALL SELECT {$columns} FROM ({$recursiveQuery}) AS recursive)";
    }
}
