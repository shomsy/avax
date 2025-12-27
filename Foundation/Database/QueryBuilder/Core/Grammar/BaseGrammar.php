<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Grammar;

use Avax\Database\Query\AST\NestedWhereNode;
use Avax\Database\Query\AST\WhereNode;
use Avax\Database\Query\QueryState;
use Avax\Database\QueryBuilder\ValueObjects\Expression;
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
     * @param QueryState $state The object containing all your query settings.
     * @return string The final SQL sentence.
     */
    public function compileSelect(QueryState $state): string
    {
        $components = [
            'select' => $this->compileColumns(state: $state),
            'from'   => $this->compileFrom(state: $state),
            'joins'  => $this->compileJoins(state: $state),
            'wheres' => $this->compileWheres(state: $state),
            'groups' => $this->compileGroups(state: $state),
            'orders' => $this->compileOrders(state: $state),
            'limit'  => $this->compileLimit(state: $state),
            'offset' => $this->compileOffset(state: $state),
        ];

        // We filter out empty strings and join the pieces with spaces.
        return implode(separator: ' ', array: array_filter(array: $components));
    }

    /**
     * Build the "SELECT column1, column2" part.
     */
    protected function compileColumns(QueryState $state): string
    {
        $select  = $state->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
        $columns = array_map(callback: fn($c) => $this->wrap(value: $c), array: $state->columns);

        return $select . implode(separator: ', ', array: $columns);
    }

    /**
     * Build the "FROM table_name" part.
     */
    protected function compileFrom(QueryState $state): string
    {
        if ($state->from) {
            return 'FROM ' . $this->wrap(value: $state->from);
        }

        return '';
    }

    /**
     * Build all the JOIN parts (e.g., INNER JOIN users ON ...).
     */
    protected function compileJoins(QueryState $state): string
    {
        if (empty($state->joins)) {
            return '';
        }

        $sql = [];

        foreach ($state->joins as $node) {
            $type  = strtoupper(string: $node->type);
            $table = $this->wrap(value: $node->table);

            if ($node->type === 'cross') {
                $sql[] = "{$type} JOIN {$table}";
                continue;
            }

            // If we have a complex ON clause (like a nested condition).
            if ($node->clause !== null) {
                $onClause = $node->clause->toSql();
                if ($onClause !== '') {
                    $sql[] = "{$type} JOIN {$table} ON {$onClause}";
                } else {
                    $sql[] = "{$type} JOIN {$table}";
                }
                continue;
            }

            // Simple "column1 = column2" join.
            if ($node->first !== null && $node->second !== null) {
                $first    = $this->wrap(value: $node->first);
                $operator = $node->operator ?? '=';
                $second   = $this->wrap(value: $node->second);

                $sql[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
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
    protected function compileWheres(QueryState $state): string
    {
        if (empty($state->wheres)) {
            return '';
        }

        $sql = [];
        foreach ($state->wheres as $i => $node) {
            $prefix  = $i === 0 ? 'WHERE ' : '';
            $boolean = $i === 0 ? '' : ($this->getWhereBoolean(node: $node) . ' ');

            // If this is a nested block: (condition1 OR condition2).
            if ($node instanceof NestedWhereNode) {
                $nestedSql = $this->compileWheres(state: $node->query->getState());
                if ($nestedSql !== '') {
                    $sql[] = $prefix . $boolean . '(' . ltrim(string: $nestedSql, characters: 'WHERE ') . ')';
                }
                continue;
            }

            if ($node instanceof WhereNode) {
                $column   = $this->wrap(value: $node->column);
                $operator = $node->operator;

                // Handle specialized null checks: IS NULL / IS NOT NULL.
                if ($node->type === 'Null') {
                    $sql[] = $prefix . $boolean . "{$column} {$operator}";
                    continue;
                }

                // Handle raw SQL provided by the user.
                if ($node->type === 'Raw') {
                    $sql[] = $prefix . $boolean . $node->column;
                    continue;
                }

                // Handle "IN" clauses: column IN (?, ?, ?).
                if (in_array(needle: $operator, haystack: ['IN', 'NOT IN']) && is_array(value: $node->value)) {
                    $count        = count(value: $node->value);
                    $placeholders = $count > 0 ? implode(separator: ', ', array: array_fill(start_index: 0, count: $count, value: '?')) : '';
                    $sql[]        = $prefix . $boolean . "{$column} {$operator} ({$placeholders})";
                    continue;
                }

                // Handle "BETWEEN" clauses: column BETWEEN ? AND ?.
                if (in_array(needle: $operator, haystack: ['BETWEEN', 'NOT BETWEEN']) && is_array(value: $node->value)) {
                    $sql[] = $prefix . $boolean . "{$column} {$operator} ? AND ?";
                    continue;
                }

                // Basic comparison: column = ?.
                $sql[] = $prefix . $boolean . "{$column} {$operator} ?";
            }
        }

        return implode(separator: ' ', array: array_filter(array: $sql));
    }

    /**
     * Build the "GROUP BY column1, column2" part.
     */
    protected function compileGroups(QueryState $state): string
    {
        if (empty($state->groups)) {
            return '';
        }

        $columns = array_map(callback: fn($column) => $this->wrap(value: $column), array: $state->groups);

        return 'GROUP BY ' . implode(separator: ', ', array: $columns);
    }

    /**
     * Build the "ORDER BY column DESC" part.
     */
    protected function compileOrders(QueryState $state): string
    {
        if (empty($state->orders)) {
            return '';
        }

        $orders = [];
        foreach ($state->orders as $node) {
            if ($node->type === 'Raw') {
                $orders[] = $node->sql ?? '';
                continue;
            }

            $column    = $this->wrap(value: $node->column);
            $direction = strtoupper(string: $node->direction);
            $orders[]  = "{$column} {$direction}";
        }

        if (empty($orders)) {
            return '';
        }

        return 'ORDER BY ' . implode(separator: ', ', array: $orders);
    }

    /**
     * Build the "LIMIT 10" part.
     */
    protected function compileLimit(QueryState $state): string
    {
        if ($state->limit) {
            return "LIMIT {$state->limit}";
        }

        return '';
    }

    /**
     * Build the "OFFSET 5" part.
     */
    protected function compileOffset(QueryState $state): string
    {
        if ($state->offset) {
            return "OFFSET {$state->offset}";
        }

        return '';
    }

    /**
     * Build a full INSERT sentence.
     */
    public function compileInsert(QueryState $state): string
    {
        $table   = $this->wrap(value: $state->from);
        $columns = implode(
            separator: ', ',
            array: array_map(
                callback: fn($c) => $this->wrap(value: $c),
                array: array_keys(array: $state->values)
            )
        );
        $values  = implode(
            separator: ', ',
            array: array_fill(
                start_index: 0,
                count: count(value: $state->values),
                value: '?'
            )
        );

        // We also collect the actual values to be sent with the query later.
        foreach ($state->values as $value) {
            $state->addBinding(value: $value);
        }

        return "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
    }

    /**
     * Build a full UPDATE sentence.
     */
    public function compileUpdate(QueryState $state): string
    {
        $table = $this->wrap(value: $state->from);

        $sets = [];
        foreach ($state->values as $column => $value) {
            $sets[] = $this->wrap(value: $column) . ' = ?';
            $state->addBinding(value: $value);
        }

        $setClause = 'SET ' . implode(separator: ', ', array: $sets);
        $wheres    = $this->compileWheres(state: $state);

        return trim(string: "UPDATE {$table} {$setClause} {$wheres}");
    }

    /**
     * Build a full DELETE sentence.
     */
    public function compileDelete(QueryState $state): string
    {
        $table  = $this->wrap(value: $state->from);
        $wheres = $this->compileWheres(state: $state);

        return trim(string: "DELETE FROM {$table} {$wheres}");
    }

    /**
     * Placeholder for the UPSERT (Insert or Update) command.
     * 
     * -- intent:
     * This is a "Hook". Because every database does Upsert differently, 
     * the Base class can't do it. Children (like MySQLGrammar) must 
     * provide the implementation.
     */
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update): string
    {
        throw new RuntimeException(message: "UPSERT is not supported by this database dialect.");
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
        return 'DROP TABLE IF EXISTS ' . $this->wrap(value: $table);
    }

    /**
     * Build the command to create a new database.
     */
    public function compileCreateDatabase(string $name): string
    {
        return "CREATE DATABASE " . $this->wrap(value: $name);
    }

    /**
     * Build the command to delete an entire database.
     */
    public function compileDropDatabase(string $name): string
    {
        return "DROP DATABASE " . $this->wrap(value: $name);
    }

    /**
     * Provide a generic random sorting snippet.
     */
    public function compileRandomOrder(): string
    {
        return 'RANDOM()';
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
            return implode(
                separator: '.',
                array: array_map(
                    callback: fn($segment) => $this->wrapSegment(segment: $segment),
                    array: explode(separator: '.', string: $value)
                )
            );
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
     * Get the boolean joiner (AND/OR) for a specific filter.
     */
    protected function getWhereBoolean(mixed $node): string
    {
        return $node->boolean ?? 'AND';
    }
}
