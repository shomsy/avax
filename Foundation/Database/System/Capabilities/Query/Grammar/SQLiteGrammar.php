<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Grammar;

use Avax\Database\System\Capabilities\Query\State\QueryState;
use Avax\Database\System\Capabilities\Query\ValueObjects\Expression;
use Override;
use RuntimeException;

/**
 * SQLite Grammar with support for:
 * - UPSERT (ON CONFLICT)
 * - RETURNING (3.35+)
 * - Window functions (3.25+)
 * - Common Table Expressions (3.26+)
 */
final class SQLiteGrammar extends BaseGrammar
{
    private bool $supportsReturning;

    private bool $supportsWindowFunctions;

    private bool $supportsCTE;

    public function __construct()
    {
        $this->supportsReturning       = $this->checkVersion(required: '3.35.0');
        $this->supportsWindowFunctions = $this->checkVersion(required: '3.25.0');
        $this->supportsCTE             = $this->checkVersion(required: '3.26.0');
    }

    private function checkVersion(string $required) : bool
    {
        if (! extension_loaded(extension: 'sqlite3')) {
            return false;
        }

        $version = SQLite3::version();

        return version_compare(version1: $version['versionString'], version2: $required, operator: '>=');
    }

    #[Override]
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update) : string
    {
        $sql = $this->compileInsert(state: $state);

        $conflictColumns = array_map(
            callback: fn ($col) => $this->wrap(value: $col),
            array   : $uniqueBy
        );
        $conflictClause  = implode(separator: ', ', array: $conflictColumns);

        $updates = [];
        foreach ($update as $column) {
            $updates[] = $this->wrap(value: $column) . ' = excluded.' . $this->wrap(value: $column);
        }

        $updateClause = implode(separator: ', ', array: $updates);

        return "{$sql} ON CONFLICT ({$conflictClause}) DO UPDATE SET {$updateClause}";
    }

    #[Override]
    public function wrap(mixed $value) : string
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
                    |> (fn ($x) => array_map(callback: fn ($segment) => $this->wrapSegment(segment: $segment), array: $x))
                    |> (static fn ($x) => implode(separator: '.', array: $x));
        }

        return $this->wrapSegment(segment: $value);
    }

    protected function wrapSegment(string $segment) : string
    {
        if ($segment === '*' || $segment === '') {
            return $segment;
        }

        return '"' . str_replace(search: '"', replace: '""', subject: $segment) . '"';
    }

    #[Override]
    public function compileRandomOrder() : string
    {
        return 'RANDOM()';
    }

    #[Override]
    public function compileTruncate(string $table) : string
    {
        return 'DELETE FROM ' . $this->wrap(value: $table);
    }

    #[Override]
    public function compileDropIfExists(string $table) : string
    {
        return 'DROP TABLE IF EXISTS ' . $this->wrap(value: $table);
    }

    #[Override]
    public function compileCreateDatabase(string $name) : string
    {
        return "ATTACH DATABASE '{$name}.db' AS {$this->wrap(value: $name)}";
    }

    #[Override]
    public function compileDropDatabase(string $name) : string
    {
        return 'DETACH DATABASE ' . $this->wrap(value: $name);
    }

    public function compileReturning(array $columns) : string
    {
        if (! $this->supportsReturning || empty($columns)) {
            return '';
        }

        $cols = array_map(callback: fn ($col) => $this->wrap(value: $col), array: $columns);

        return 'RETURNING ' . implode(separator: ', ', array: $cols);
    }

    public function supportsWindowFunctions() : bool
    {
        return $this->supportsWindowFunctions;
    }

    public function supportsCTE() : bool
    {
        return $this->supportsCTE;
    }

    public function supportsReturning() : bool
    {
        return $this->supportsReturning;
    }

    public function compileWindowFunction(string $function, string $partitionBy = '', string $orderBy = '') : string
    {
        if (! $this->supportsWindowFunctions) {
            throw new RuntimeException(message: 'Window functions require SQLite 3.25.0+');
        }

        $sql = $function . '(';

        if ($partitionBy !== '') {
            $partitionColumns = array_map(
                callback: fn ($col) => $this->wrap(value: $col),
                array   : explode(separator: ',', string: $partitionBy)
            );
            $sql              .= 'PARTITION BY ' . implode(separator: ', ', array: $partitionColumns);
        }

        if ($orderBy !== '') {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        $sql .= ')';

        return $sql;
    }

    public function compileWithRecursive(string $name, string $columns, string $initialQuery, string $recursiveQuery) : string
    {
        if (! $this->supportsCTE) {
            throw new RuntimeException(message: 'CTE requires SQLite 3.26.0+');
        }

        return "WITH RECURSIVE {$name} AS ({$initialQuery} UNION ALL {$recursiveQuery})";
    }

    public function compileRegexp(string $column, string $pattern) : string
    {
        return "{$this->wrap(value: $column)} REGEXP '{$pattern}'";
    }
}
