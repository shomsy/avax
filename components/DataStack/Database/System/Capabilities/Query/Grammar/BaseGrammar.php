<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST\NestedWhereNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST\WhereNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Avax\Components\DataStack\Database\System\Capabilities\Query\ValueObjects\Expression;
use RuntimeException;

/**
 * The "Foundation of Language" (Base Grammar).
 *
 * -- what is it?
 * This is the parent class for all SQL Grammars (MySQL, Postgres, etc.).
 * It contains the "Common Rules" of SQL that almost all databases share.
 * While MySQLGrammar handles backticks, this class handles the actual
 * structure of a SELECT, INSERT, or UPDATE sentence.
 *
 * -- how to imagine it:
 * Think of "Latin" as the base for many European languages. BaseGrammar
 * is the "Latin" of SQL — it defines the general structure of how
 * sentences are built. Specific grammars (like MySQL) then add their
 * own specific "Accents" or "Slang" (like backticks instead of double
 * quotes).
 *
 * -- why this exists:
 * 1. Code Reuse: We don't want to rewrite the logic for building a `WHERE`
 *    clause for every single database. This class does it once for everyone.
 * 2. Predictability: It ensures that no matter which database you use, the
 *    QueryBuilder produces a structure that makes sense.
 * 3. Flexibility: By making this class `abstract`, we FORCE specific
 *    databases to implement their own "Accents" (like how to wrap names).
 *
 * -- mental models:
 * - "Compiler": It "Compiles" an object representing a query (QueryState)
 *    into a plain string of SQL.
 * - "Idempotent": Running the same compilation twice with the same input
 *    will ALWAYS produce the exact same SQL output.
 */
abstract class BaseGrammar implements GrammarInterface
{
    /**
     * Build a full SELECT sentence from a query object.
     *
     * -- how it works:
     * It builds the sentence piece by piece:
     * 1. SELECT columns...
     * 2. FROM table...
     * 3. JOIN others...
     * 4. WHERE conditions...
     * ...and so on.
     *
     * @param QueryState $queryState The object containing all your query settings.
     *
     * @return string The final SQL sentence.
     */
    public function compileSelect(QueryState $queryState) : string
    {
        $components = [
            'ctes'   => $this->compileCtes(state: $queryState),
            'select' => $this->compileColumns(state: $queryState),
            'from'   => $this->compileFrom(state: $queryState),
            'joins'  => $this->compileJoins(state: $queryState),
            'wheres' => $this->compileWheres(state: $queryState),
            'groups' => $this->compileGroups(state: $queryState),
            'orders' => $this->compileOrders(state: $queryState),
            'limit'  => $this->compileLimit(state: $queryState),
            'offset' => $this->compileOffset(state: $queryState),
        ];

        // We filter out empty strings and join the pieces with spaces.
        return implode(separator: ' ', array: array_filter(array: $components));
    }

    protected function compileCtes(QueryState $queryState) : string
    {
        if ($queryState->ctes === []) {
            return '';
        }

        $ctes = [];
        $recursive = false;

        foreach ($queryState->ctes as $cte) {
            $name  = $this->wrap($cte['name']);
            $query = $cte['query'] instanceof QueryState ? $this->compileSelect($cte['query']) : (string) $cte['query'];
            $ctes[] = sprintf('%s AS (%s)', $name, $query);
            if ($cte['recursive']) {
                $recursive = true;
            }
        }

        return 'WITH ' . ($recursive ? 'RECURSIVE ' : '') . implode(', ', $ctes);
    }

    /**
     * Securely wrap column or table names in quotes.
     *
     * -- why this exists:
     * To prevent "SQL Keyword Collisions". If you have a column named
     * `order`, SQL will get confused unless we wrap it in quotes (`"order"`).
     */
    public function wrap(mixed $value): string
    {
        if ($value instanceof Expression) {
            return $value->getValue();
        }

        $value = (string) $value;

        if ($value === '*' || str_contains(haystack: $value, needle: '(')) {
            return $value;
        }

        // Handle names with dots (e.g., 'users.name').
        if (str_contains(haystack: $value, needle: '.')) {
            return explode(separator: '.', string: $value)
                    |> (fn ($x) : array => array_map(callback: fn (string $segment) : string => $this->wrapSegment(segment: $segment), array: $x))
                    |> (static fn ($x) : string => implode(separator: '.', array: $x));
        }

        return $this->wrapSegment(segment: $value);
    }

    /**
     * Securely wrap a single part of a name (e.g., the 'users' bit).
     */
    protected function wrapSegment(string $segment): string
    {
        if ($segment === '*') {
            return $segment;
        }

        // Default is to use double quotes (") which is standard SQL.
        return '"' . str_replace(search: '"', replace: '""', subject: $segment) . '"';
    }

    /**
     * Build the "SELECT column1, column2" part.
     */
    protected function compileColumns(QueryState $queryState) : string
    {
        $select  = $queryState->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
        $columns = array_map(callback: function ($c) use ($queryState) : string {
            if (is_string($c) && isset($queryState->windows[$c])) {
                return $this->wrap($c) . ' OVER ' . $this->wrap($queryState->windows[$c]);
            }

            return $this->wrap(value: $c);
        },                   array   : $queryState->columns);

        return $select . implode(separator: ', ', array: $columns);
    }

    /**
     * Build the "FROM table_name" part.
     */
    protected function compileFrom(QueryState $queryState) : string
    {
        if ($queryState->from) {
            return 'FROM ' . $this->wrap(value: $queryState->from);
        }

        return '';
    }

    /**
     * Build all the JOIN parts (e.g., INNER JOIN users ON ...).
     */
    protected function compileJoins(QueryState $queryState) : string
    {
        if ($queryState->joins === []) {
            return '';
        }

        $sql = [];

        foreach ($queryState->joins as $node) {
            $type      = strtoupper(string: (string) $node->type);
            $table = $this->wrap(value: $node->table);

            if ($node->type === 'cross') {
                $sql[] = sprintf('%s JOIN %s', $type, $table);

                continue;
            }

            // If we have a complex ON clause (like a nested condition).
            if ($node->clause !== null) {
                $onClause = $node->clause->toSql();
                $sql[] = $onClause !== '' ? sprintf('%s JOIN %s ON %s', $type, $table, $onClause) : sprintf('%s JOIN %s', $type, $table);

                continue;
            }

            // Simple "column1 = column2" join.
            if ($node->first !== null && $node->second !== null) {
                $first    = $this->wrap(value: $node->first);
                $operator = $node->operator ?? '=';
                $second   = $this->wrap(value: $node->second);

                $sql[] = sprintf('%s JOIN %s ON %s %s %s', $type, $table, $first, $operator, $second);
            }
        }

        return implode(separator: ' ', array: $sql);
    }

    /**
     * Build the filter part (WHERE column = ? AND ...).
     *
     * -- how it works:
     * It handles "Nested" filters by putting them in parentheses.
     * It also uses "?" placeholders for values to keep the SQL secure.
     */
    protected function compileWheres(QueryState $queryState) : string
    {
        if ($queryState->wheres === []) {
            return '';
        }

        $sql = [];
        foreach ($queryState->wheres as $i => $node) {
            $prefix  = $i === 0 ? 'WHERE ' : '';
            $boolean = $i === 0 ? '' : ($this->getWhereBoolean(node: $node) . ' ');

            // If this is a nested block: (condition1 OR condition2).
            if ($node instanceof NestedWhereNode) {
                $nestedSql = $this->compileWheres(state: $node->query);
                if ($nestedSql !== '') {
                    $sql[] = $prefix . $boolean . '(' . ltrim(string: $nestedSql, characters: 'WHERE ') . ')';
                }

                continue;
            }

            if ($node instanceof WhereNode) {
                $column = $this->wrap(value: $node->column);
                $operator = $node->operator;

                // Handle specialized null checks: IS NULL / IS NOT NULL.
                if ($node->type === 'Null') {
                    $sql[] = $prefix . $boolean . sprintf('%s %s', $column, $operator);

                    continue;
                }

                // Handle raw SQL provided by the user.
                if ($node->type === 'Raw') {
                    $sql[] = $prefix . $boolean . $node->column;

                    continue;
                }

                // Handle "IN" clauses: column IN (?, ?, ?).
                if (in_array(true, needle: $operator, haystack: ['IN', 'NOT IN']) && is_array(value: $node->value)) {
                    $count        = count(value: $node->value);
                    $placeholders = $count > 0 ? implode(separator: ', ', array: array_fill(start_index: 0, count: $count, value: '?')) : '';
                    $sql[] = $prefix . $boolean . sprintf('%s %s (%s)', $column, $operator, $placeholders);

                    continue;
                }

                // Handle "BETWEEN" clauses: column BETWEEN ? AND ?.
                if (in_array(true, needle: $operator, haystack: ['BETWEEN', 'NOT BETWEEN']) && is_array(value: $node->value)) {
                    $sql[] = $prefix . $boolean . sprintf('%s %s ? AND ?', $column, $operator);

                    continue;
                }

                // Basic comparison: column = ?.
                $sql[] = $prefix . $boolean . sprintf('%s %s ?', $column, $operator);
            }
        }

        return implode(separator: ' ', array: array_filter(array: $sql));
    }

    /**
     * Get the boolean joiner (AND/OR) for a specific filter.
     */
    protected function getWhereBoolean(mixed $node): string
    {
        return $node->boolean ?? 'AND';
    }

    /**
     * Build the "GROUP BY column1, column2" part.
     */
    protected function compileGroups(QueryState $queryState) : string
    {
        if ($queryState->groups === []) {
            return '';
        }

        $columns = array_map(callback: fn ($column) : string => $this->wrap(value: $column), array: $queryState->groups);

        return 'GROUP BY ' . implode(separator: ', ', array: $columns);
    }

    /**
     * Build the "ORDER BY column DESC" part.
     */
    protected function compileOrders(QueryState $queryState) : string
    {
        if ($queryState->orders === []) {
            return '';
        }

        $orders = [];
        foreach ($queryState->orders as $node) {
            if ($node->type === 'Raw') {
                $orders[] = $node->sql ?? '';

                continue;
            }

            $column    = $this->wrap(value: $node->column);
            $direction = strtoupper(string: (string) $node->direction);
            $orders[] = sprintf('%s %s', $column, $direction);
        }

        if ($orders === []) {
            return '';
        }

        return 'ORDER BY ' . implode(separator: ', ', array: $orders);
    }

    /**
     * Build the "LIMIT 10" part.
     */
    protected function compileLimit(QueryState $queryState) : string
    {
        if ($queryState->limit) {
            return 'LIMIT ' . $queryState->limit;
        }

        return '';
    }

    /**
     * Build the "OFFSET 5" part.
     */
    protected function compileOffset(QueryState $queryState) : string
    {
        if ($queryState->offset) {
            return 'OFFSET ' . $queryState->offset;
        }

        return '';
    }

    /**
     * Build a full INSERT sentence.
     */
    public function compileInsert(QueryState $queryState) : string
    {
        $table = $this->wrap(value: $queryState->from);
        $rows  = $this->normalizeInsertRows(values: $queryState->values);

        if ($rows === []) {
            throw new RuntimeException(message: 'INSERT compilation requires at least one row of values.');
        }

        $columns = array_keys(array: $rows[0])
                |> (fn ($x) : array => array_map(callback: fn ($c) : string => $this->wrap(value: $c), array: $x))
                |> (static fn ($x) : string => implode(separator: ', ', array: $x));

        $valueGroups = array_map(
            callback: static function (array $row): string {
                $placeholders = array_map(
                    callback: static fn (mixed $value) : string => $value instanceof Expression ? $value->getValue() : '?',
                    array   : array_values(array: $row),
                );

                return '(' . implode(separator: ', ', array: $placeholders) . ')';
            },
            array   : $rows,
        );

        // noinspection SqlNoDataSourceInspection
        return sprintf('INSERT INTO %s (%s) VALUES ', $table, $columns) . implode(separator: ', ', array: $valueGroups);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalizeInsertRows(array $values): array
    {
        if ($values === []) {
            return [];
        }

        if (array_is_list(array: $values) && is_array(value: $values[0] ?? null)) {
            /** @var list<array<string, mixed>> $values */
            return $values;
        }

        /** @var array<string, mixed> $values */
        return [$values];
    }

    /**
     * Build a full UPDATE sentence.
     */
    public function compileUpdate(QueryState $queryState) : string
    {
        $table  = $this->wrap(value: $queryState->from);

        $sets = [];
        foreach ($queryState->values as $column => $value) {
            $sets[] = $this->wrap(value: $column) . ' = ' . ($value instanceof Expression ? $value->getValue() : '?');
        }

        $setClause = 'SET ' . implode(separator: ', ', array: $sets);
        $wheres = $this->compileWheres(state: $queryState);

        // noinspection SqlNoDataSourceInspection
        return trim(string: sprintf('UPDATE %s %s %s', $table, $setClause, $wheres));
    }

    /**
     * Build a full DELETE sentence.
     */
    public function compileDelete(QueryState $queryState) : string
    {
        $table  = $this->wrap(value: $queryState->from);
        $wheres = $this->compileWheres(state: $queryState);

        // noinspection SqlNoDataSourceInspection
        return trim(string: sprintf('DELETE FROM %s %s', $table, $wheres));
    }

    /**
     * Build an UPSERT command when the database dialect supports it.
     *
     * The base grammar rejects this deliberately because UPSERT syntax is
     * vendor-specific; concrete grammars must own their dialect form.
     */
    public function compileUpsert(QueryState $queryState, array $uniqueBy, array $update) : string
    {
        throw new RuntimeException(message: 'UPSERT is not supported by this database dialect.');
    }

    /**
     * Build the command to completely empty a table.
     */
    public function compileTruncate(string $table): string
    {
        return 'TRUNCATE ' . $this->wrap(value: $table);
    }

    /**
     * Build the command to delete a table if it exists.
     */
    public function compileDropIfExists(string $table): string
    {
        // noinspection SqlNoDataSourceInspection
        return 'DROP TABLE IF EXISTS ' . $this->wrap(value: $table);
    }

    /**
     * Build the command to create a new database.
     */
    public function compileCreateDatabase(string $name): string
    {
        return 'CREATE DATABASE ' . $this->wrap(value: $name);
    }

    /**
     * Build the command to delete an entire database.
     */
    public function compileDropDatabase(string $name): string
    {
        return 'DROP DATABASE ' . $this->wrap(value: $name);
    }

    /**
     * Provide a generic random sorting snippet.
     */
    public function compileRandomOrder(): string
    {
        return 'RANDOM()';
    }
}
