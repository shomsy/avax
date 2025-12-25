<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Grammar;

use Avax\Database\Query\QueryState;
use Avax\Database\QueryBuilder\ValueObjects\Expression;

/**
 * Base implementation of SQL compilation logic shared across most RDBMS dialects.
 *
 * -- intent: provide a foundation for building standardized SQL strings from query metadata.
 */
abstract class BaseGrammar implements GrammarInterface
{
    /**
     * Compile the components of a SELECT statement into a cohesive string.
     *
     * -- intent: coordinate the sequential compilation of columns, source, joins, and filters.
     *
     * @param QueryState $state Current builder parameters
     *
     * @return string
     */
    public function compileSelect(QueryState $state) : string
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

        return implode(separator: ' ', array: array_filter(array: $components));
    }

    /**
     * Compile the column list for the retrieval query.
     *
     * -- intent: transform technical column names into a comma-separated and wrapped SQL list.
     *
     * @param QueryState $state Target state
     *
     * @return string
     */
    protected function compileColumns(QueryState $state) : string
    {
        $select  = $state->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
        $columns = array_map(callback: fn ($c) => $this->wrap(value: $c), array: $state->columns);

        return $select . implode(separator: ', ', array: $columns);
    }

    /**
     * Ensure a database identifier is properly escaped according to standard SQL.
     *
     * -- intent: provide generic wrapping logic to be overridden by specific dialects.
     *
     * @param mixed $value Technical name or Expression
     *
     * @return string
     */
    public function wrap(mixed $value) : string
    {
        // If it's an Expression, return raw value
        if ($value instanceof Expression) {
            return $value->getValue();
        }

        $value = (string) $value;

        // Don't wrap wildcards or function calls
        if ($value === '*' || str_contains(haystack: $value, needle: '(')) {
            return $value;
        }

        // Handle table.column notation
        if (str_contains(haystack: $value, needle: '.')) {
            return implode(separator: '.', array: array_map(callback: fn ($segment) => $this->wrapSegment($segment), array: explode(separator: '.', string: $value)));
        }

        return $this->wrapSegment($value);
    }

    /**
     * Wrap a single identifier segment.
     *
     * @param string $segment
     *
     * @return string
     */
    protected function wrapSegment(string $segment) : string
    {
        if ($segment === '*') {
            return $segment;
        }

        return '"' . str_replace(search: '"', replace: '""', subject: $segment) . '"';
    }

    /**
     * Compile the source table for the query.
     *
     * -- intent: transform the internal table identifier into a wrapped SQL fragment.
     *
     * @param QueryState $state Target state
     *
     * @return string
     */
    protected function compileFrom(QueryState $state) : string
    {
        if ($state->from) {
            return 'FROM ' . $this->wrap(value: $state->from);
        }

        return '';
    }

    /**
     * Compile all JOIN clauses into SQL fragments.
     *
     * -- intent: transform join metadata into proper SQL JOIN syntax.
     *
     * @param QueryState $state Target state
     *
     * @return string
     */
    protected function compileJoins(QueryState $state) : string
    {
        if (empty($state->joins)) {
            return '';
        }

        $sql = [];

        foreach ($state->joins as $join) {
            $type  = strtoupper($join['type']);
            $table = $this->wrap($join['table']);

            // CROSS JOIN doesn't need ON clause
            if ($join['type'] === 'cross') {
                $sql[] = "{$type} JOIN {$table}";
                continue;
            }

            $first    = $this->wrap($join['first']);
            $operator = $join['operator'] ?? '=';
            $second   = $this->wrap($join['second']);

            $sql[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        }

        return implode(' ', $sql);
    }

    /**
     * Compile all logical filters (WHERE clauses) into a single SQL clause.
     *
     * -- intent: provide a structured transformation of Condition objects into SQL criteria.
     *
     * @param QueryState $state Target state
     *
     * @return string
     */
    protected function compileWheres(QueryState $state) : string
    {
        if (empty($state->wheres)) {
            return '';
        }

        $sql = [];
        foreach ($state->wheres as $i => $where) {
            $prefix = $i === 0 ? 'WHERE ' : $where->boolean . ' ';
            $sql[]  = $prefix . $this->wrap(value: $where->column) . " {$where->operator} ?";
        }

        return implode(separator: ' ', array: $sql);
    }

    /**
     * Compile GROUP BY clauses into SQL.
     *
     * -- intent: transform grouping metadata into proper SQL GROUP BY syntax.
     *
     * @param QueryState $state Target state
     *
     * @return string
     */
    protected function compileGroups(QueryState $state) : string
    {
        if (empty($state->groups)) {
            return '';
        }

        $columns = array_map(fn ($column) => $this->wrap($column), $state->groups);

        return 'GROUP BY ' . implode(', ', $columns);
    }

    /**
     * Compile ORDER BY clauses into SQL.
     *
     * -- intent: transform sorting metadata into proper SQL ORDER BY syntax.
     *
     * @param QueryState $state Target state
     *
     * @return string
     */
    protected function compileOrders(QueryState $state) : string
    {
        if (empty($state->orders)) {
            return '';
        }

        $orders = [];
        foreach ($state->orders as $order) {
            $column    = $this->wrap($order['column']);
            $direction = strtoupper($order['direction'] ?? 'ASC');
            $orders[]  = "{$column} {$direction}";
        }

        return 'ORDER BY ' . implode(', ', $orders);
    }

    /**
     * Compile the LIMIT clause for result set restriction.
     *
     * -- intent: transform the limit metadata into standard SQL LIMIT syntax.
     *
     * @param QueryState $state Target state
     *
     * @return string
     */
    protected function compileLimit(QueryState $state) : string
    {
        if ($state->limit) {
            return "LIMIT {$state->limit}";
        }

        return '';
    }

    /**
     * Compile the OFFSET clause for result set pagination.
     *
     * -- intent: transform the offset metadata into standard SQL OFFSET syntax.
     *
     * @param QueryState $state Target state
     *
     * @return string
     */
    protected function compileOffset(QueryState $state) : string
    {
        if ($state->offset) {
            return "OFFSET {$state->offset}";
        }

        return '';
    }

    /**
     * Compile an INSERT statement based on the provided state values.
     *
     * -- intent: transform a simple data map into a valid SQL insertion command.
     *
     * @param QueryState $state Target state
     *
     * @return string
     */
    public function compileInsert(QueryState $state) : string
    {
        $table   = $this->wrap(value: $state->from);
        $columns = implode(
            separator: ', ',
            array    : array_map(
                           callback: fn ($c) => $this->wrap(value: $c),
                           array   : array_keys(array: $state->values)
                       )
        );
        $values  = implode(
            separator: ', ',
            array    : array_fill(
                           start_index: 0,
                           count      : count(value: $state->values),
                           value      : '?'
                       )
        );

        foreach ($state->values as $value) {
            $state->addBinding(value: $value);
        }

        return "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
    }

    /**
     * Compile an UPDATE statement based on the provided state values and filters.
     *
     * -- intent: transform mutation data and criteria into a valid SQL modification command.
     *
     * @param QueryState $state Target state
     *
     * @return string
     */
    public function compileUpdate(QueryState $state) : string
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
     * Compile a DELETE statement based on the provided filter state.
     *
     * -- intent: transform query criteria into a destructive SQL command.
     *
     * @param QueryState $state Target state
     *
     * @return string
     */
    public function compileDelete(QueryState $state) : string
    {
        $table  = $this->wrap(value: $state->from);
        $wheres = $this->compileWheres(state: $state);

        return trim(string: "DELETE FROM {$table} {$wheres}");
    }

    /**
     * Compile the SQL to create a new database.
     *
     * @param string $name Database name
     *
     * @return string
     */
    public function compileCreateDatabase(string $name) : string
    {
        return "CREATE DATABASE " . $this->wrap(value: $name);
    }

    /**
     * Compile the SQL to drop a database.
     *
     * @param string $name Database name
     *
     * @return string
     */
    public function compileDropDatabase(string $name) : string
    {
        return "DROP DATABASE " . $this->wrap(value: $name);
    }
}
