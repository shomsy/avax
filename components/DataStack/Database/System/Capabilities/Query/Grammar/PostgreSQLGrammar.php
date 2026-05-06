<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Avax\Components\DataStack\Database\System\Capabilities\Query\ValueObjects\Expression;
use Override;

/**
 * PostgreSQL Grammar with advanced features:
 * - ON CONFLICT (upsert)
 * - RETURNING clauses
 * - Window functions (ROW_NUMBER, RANK, LAG, LEAD)
 * - WITH RECURSIVE (CTE)
 * - JSONB, ARRAY, HSTORE types
 * - advisory locks
 */
final class PostgreSQLGrammar extends BaseGrammar
{
    public function compileUpsert(QueryState $queryState, array $uniqueBy, array $update): string
    {
        $sql = $this->compileInsert($queryState);

        $conflictColumns = array_map(
            callback: fn ($col): string => $this->wrap(value: $col),
            array   : $uniqueBy,
        );
        $conflictClause = implode(separator: ', ', array: $conflictColumns);

        $updates = [];
        foreach ($update as $column) {
            $updates[] = $this->wrap(value: $column).' = EXCLUDED.'.$this->wrap(value: $column);
        }

        $updateClause = implode(separator: ', ', array: $updates);

        return sprintf('%s ON CONFLICT (%s) DO UPDATE SET %s', $sql, $conflictClause, $updateClause);
    }

    public function wrap(mixed $value): string
    {
        if ($value instanceof Expression) {
            return $value->getValue();
        }

        $value = (string) $value;

        if ($value === '*' || str_contains(haystack: $value, needle: '(')) {
            return $value;
        }

        if (str_contains(haystack: $value, needle: '.')) {
            return explode(separator: '.', string: $value)
                    |> (fn ($x): array => array_map(callback: fn (string $segment): string => $this->wrapSegment(segment: $segment), array: $x))
                    |> (static fn ($x): string => implode(separator: '.', array: $x));
        }

        return $this->wrapSegment(segment: $value);
    }

    #[Override]
    protected function wrapSegment(string $segment): string
    {
        if ($segment === '*' || $segment === '') {
            return $segment;
        }

        return '"'.str_replace(search: '"', replace: '""', subject: $segment).'"';
    }

    #[Override]
    public function compileRandomOrder(): string
    {
        return 'RANDOM()';
    }

    #[Override]
    public function compileTruncate(string $table): string
    {
        return 'TRUNCATE TABLE '.$this->wrap(value: $table).' RESTART IDENTITY';
    }

    #[Override]
    public function compileDropIfExists(string $table): string
    {
        return 'DROP TABLE IF EXISTS '.$this->wrap(value: $table).' CASCADE';
    }

    public function compileReturning(array $columns): string
    {
        if ($columns === []) {
            return '';
        }

        $cols = array_map(callback: fn ($col): string => $this->wrap(value: $col), array: $columns);

        return 'RETURNING '.implode(separator: ', ', array: $cols);
    }

    public function compileWindowFunction(string $function, ?string $partitionBy = null, string $orderBy = ''): string
    {
        $partitionBy ??= '';
        $sql = $function.'(';

        if ($partitionBy !== '') {
            $partitionColumns = implode(separator: ', ', array: array_map(
                callback: fn ($col): string => $this->wrap(value: $col),
                array   : explode(separator: ',', string: $partitionBy),
            ));
            $sql .= 'PARTITION BY '.$partitionColumns;
        }

        if ($orderBy !== '') {
            $sql .= ' ORDER BY '.$orderBy;
        }

        return $sql.')';
    }

    public function compileWithRecursive(string $name, string $columns, string $initialQuery, string $recursiveQuery): string
    {
        $sql = sprintf('WITH RECURSIVE %s AS (', $name);

        return $sql.($initialQuery.' UNION ALL '.$recursiveQuery.')');
    }

    public function compileAdvisoryLock(string $lockId): string
    {
        return sprintf('SELECT pg_advisory_lock(%s)', $lockId);
    }

    public function compileAdvisoryUnlock(string $lockId): string
    {
        return sprintf('SELECT pg_advisory_unlock(%s)', $lockId);
    }

    public function compileJsonbExtractPath(string $column, string $path): string
    {
        return sprintf("%s->>'%s'", $this->wrap(value: $column), $path);
    }

    public function compileJsonbContains(string $column, string $value): string
    {
        return sprintf("%s @> '%s'", $this->wrap(value: $column), $value);
    }

    public function compileJsonbContainedBy(string $column, string $value): string
    {
        return sprintf("%s <@ '%s'", $this->wrap(value: $column), $value);
    }

    public function compileArrayContains(string $column, array $values): string
    {
        $valueList = '{'.implode(separator: ',', array: $values).'}';

        return sprintf("%s @> '%s'", $this->wrap(value: $column), $valueList);
    }

    public function compileArrayOverlap(string $column, array $values): string
    {
        $valueList = '{'.implode(separator: ',', array: $values).'}';

        return sprintf("%s && '%s'", $this->wrap(value: $column), $valueList);
    }
}
