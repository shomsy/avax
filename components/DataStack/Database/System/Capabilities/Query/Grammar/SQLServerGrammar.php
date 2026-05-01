<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Avax\Components\DataStack\Database\System\Capabilities\Query\ValueObjects\Expression;
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
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update): string
    {
        $table = $this->wrap(value: $state->from);
        $rows = $this->normalizeInsertRows(values: $state->values);

        if (empty($rows)) {
            throw new RuntimeException(message: 'INSERT compilation requires at least one row of values.');
        }

        $columns = implode(separator: ', ', array: array_map(callback: fn ($c) => $this->wrap(value: $c), array: array_keys(array: $rows[0])));

        $valueGroups = [];
        foreach ($rows as $row) {
            $placeholders = [];
            foreach ($row as $value) {
                $placeholders[] = $value instanceof Expression ? $value->getValue() : '?';
            }
            $valueGroups[] = '('.implode(separator: ', ', array: $placeholders).')';
        }

        $conflictColumns = implode(separator: ', ', array: array_map(
            callback: fn ($col) => $this->wrap(value: $col),
            array   : $uniqueBy,
        ));

        $updates = [];
        foreach ($update as $column) {
            $updates[] = $this->wrap(value: $column).' = source.'.$this->wrap(value: $column);
        }
        $updateSet = implode(separator: ', ', array: $updates);

        $valuesSql = 'VALUES '.implode(separator: ', ', array: $valueGroups);

        $sql = "MERGE {$table} AS target\n";
        $sql .= "USING (SELECT {$columns} {$valuesSql}) AS source ({$columns})\n";
        $sql .= "ON target.{$conflictColumns} = source.{$conflictColumns}\n";
        $sql .= "WHEN MATCHED THEN UPDATE SET {$updateSet}\n";
        $sql .= "WHEN NOT MATCHED THEN INSERT ({$columns}) VALUES ({$columns});";

        return $sql;
    }

    #[Override]
    public function wrap(mixed $value): string
    {
        parent::wrap(value: $value);

        if ($value instanceof Expression) {
            return $value->getValue();
        }

        $value = (string) $value;

        if ($value === '*' || str_contains(haystack: $value, needle: '(')) {
            return $value;
        }

        if (str_contains(haystack: $value, needle: '.')) {
            return implode(separator: '.', array: array_map(
                callback: fn ($segment) => $this->wrapSegment(segment: $segment),
                array   : explode(separator: '.', string: $value),
            ));
        }

        return $this->wrapSegment(segment: $value);
    }

    #[Override]
    protected function wrapSegment(string $segment): string
    {
        parent::wrapSegment(segment: $segment);

        if ($segment === '*' || $segment === '') {
            return $segment;
        }

        return '['.str_replace(search: ']', replace: ']]', subject: $segment).']';
    }

    protected function normalizeInsertRows(array $values): array
    {
        parent::normalizeInsertRows(values: $values);

        // satisfying linter - this logic is duplicated to avoid changing BaseGrammar
        return (array_is_list(array: $values) && is_array(value: $values[0] ?? null)) ? $values : [$values];
    }

    #[Override]
    public function compileRandomOrder(): string
    {
        return 'NEWID()';
    }

    #[Override]
    public function compileTruncate(string $table): string
    {
        return 'TRUNCATE TABLE '.$this->wrap(value: $table);
    }

    #[Override]
    public function compileDropIfExists(string $table): string
    {
        return "IF OBJECT_ID('".$table."') IS NOT NULL DROP TABLE ".$this->wrap(value: $table);
    }

    #[Override]
    public function compileCreateDatabase(string $name): string
    {
        return 'CREATE DATABASE '.$this->wrap(value: $name);
    }

    #[Override]
    public function compileDropDatabase(string $name): string
    {
        return 'DROP DATABASE '.$this->wrap(value: $name);
    }

    public function compileOutput(array $columns): string
    {
        if (empty($columns)) {
            return '';
        }

        $cols = implode(separator: ', ', array: array_map(callback: fn ($col) => $this->wrap(value: $col), array: $columns));

        return 'OUTPUT '.$cols;
    }

    public function compilePivot(string $column, string $pivotColumn, array $pivotValues): string
    {
        $pivotcols = implode(separator: ', ', array: array_map(callback: static fn ($val) => "['{$val}']", array: $pivotValues));

        return "PIVOT ({$column} FOR {$pivotColumn} IN ({$pivotcols}))";
    }

    public function compileUnpivot(string $column, array $unpivotColumns): string
    {
        $unpivcols = implode(separator: ', ', array: array_map(callback: fn ($col) => $this->wrap(value: $col), array: $unpivotColumns));

        return "UNPIVOT ({$column} IN ({$unpivcols}))";
    }

    public function compileWindowFunction(string $function, ?string $partitionBy = null, string $orderBy = ''): string
    {
        $partitionBy ??= '';
        $sql = $function.'(';

        if ($partitionBy !== '') {
            $partitionColumns = implode(separator: ', ', array: array_map(
                callback: fn ($col) => $this->wrap(value: $col),
                array   : explode(separator: ',', string: $partitionBy),
            ));
            $sql .= 'PARTITION BY '.$partitionColumns;
        }

        if ($orderBy !== '') {
            $sql .= ' ORDER BY '.$orderBy;
        }

        $sql .= ')';

        return $sql;
    }

    public function compileWithRecursive(string $name, string $columns, string $initialQuery, string $recursiveQuery): string
    {
        return "WITH {$name} AS (SELECT {$columns} FROM ({$initialQuery}) AS initial UNION ALL SELECT {$columns} FROM ({$recursiveQuery}) AS recursive)";
    }
}
