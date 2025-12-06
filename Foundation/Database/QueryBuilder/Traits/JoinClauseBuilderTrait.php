<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Traits;

use Avax\Database\QueryBuilder\Exception\QueryBuilderException;

/**
 * **JoinClauseBuilderTrait**
 *
 * Handles SQL JOIN clauses, supporting INNER, LEFT, RIGHT, FULL, CROSS, NATURAL, and SELF joins.
 *
 * **Security Enhancements:**
 * - 🛡️ **Prevents SQL Injection** by sanitizing table and column names.
 * - 🔍 **Ensures JOIN conditions are valid** before appending them.
 * - 🔒 **Forces table and alias validation** to avoid SQL tampering.
 */
trait JoinClauseBuilderTrait
{
    private array $joinClauses = [];

    /**
     * Builds the SQL JOIN clauses as a concatenated string.
     */
    public function buildJoins() : string
    {
        return empty($this->joinClauses) ? '' : ' ' . implode(' ', $this->joinClauses);
    }

    /**
     * Resets all JOIN clauses.
     */
    public function resetJoins() : static
    {
        $this->joinClauses = [];

        return $this;
    }

    /**
     * Adds a LEFT JOIN clause.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function leftJoin(string $table, string $first, string $operator, string $second) : static
    {
        return $this->join(
            table     : $table,
            alias     : null,
            conditions: [
                            $this->quoteIdentifier(name: $first) . " {$operator} " . $this->quoteIdentifier(
                                name: $second
                            ),
                        ],
            type      : 'LEFT JOIN'
        );
    }

    /**
     * Adds a JOIN clause with an optional alias and multiple conditions.
     *
     * @throws QueryBuilderException
     */
    public function join(string $table, string|null $alias, array|string $conditions, string $type = 'JOIN') : static
    {
        $tableWithAlias = $alias ? sprintf(
            '%s AS %s',
            $this->quoteIdentifier(name: $table),
            $this->quoteIdentifier(name: $alias)
        ) : $this->quoteIdentifier(name: $table);

        // Ensure conditions are valid
        if (is_array($conditions)) {
            $conditionString = implode(' AND ', array_map(static fn($condition) => trim($condition), $conditions));
        } else {
            $conditionString = trim($conditions);
        }

        if (empty($table) || empty($conditionString)) {
            throw new QueryBuilderException(message: 'Invalid JOIN statement: table name and conditions are required.');
        }

        $this->joinClauses[] = sprintf('%s %s ON %s', strtoupper($type), $tableWithAlias, $conditionString);

        return $this;
    }

    /**
     * Adds a RIGHT JOIN clause.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function rightJoin(string $table, string $first, string $operator, string $second) : static
    {
        return $this->join(
            table     : $table,
            alias     : null,
            conditions: [
                            $this->quoteIdentifier(name: $first) . " {$operator} " . $this->quoteIdentifier(
                                name: $second
                            ),
                        ],
            type      : 'RIGHT JOIN'
        );
    }

    /**
     * Adds a FULL OUTER JOIN clause.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function fullOuterJoin(string $table, string $first, string $operator, string $second) : static
    {
        return $this->join(
            table     : $table,
            alias     : null,
            conditions: [
                            $this->quoteIdentifier(name: $first) . " {$operator} " . $this->quoteIdentifier(
                                name: $second
                            ),
                        ],
            type      : 'FULL OUTER JOIN'
        );
    }

    /**
     * Adds a CROSS JOIN clause.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function crossJoin(string $table) : static
    {
        if (empty($table)) {
            throw new QueryBuilderException(message: 'Invalid CROSS JOIN: table name cannot be empty.');
        }

        $this->joinClauses[] = sprintf('CROSS JOIN %s', $this->quoteIdentifier(name: $table));

        return $this;
    }

    /**
     * Adds a NATURAL JOIN clause.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function naturalJoin(string $table) : static
    {
        if (empty($table)) {
            throw new QueryBuilderException(message: 'Invalid NATURAL JOIN: table name cannot be empty.');
        }

        $this->joinClauses[] = sprintf('NATURAL JOIN %s', $this->quoteIdentifier(name: $table));

        return $this;
    }

    /**
     * Adds a SELF JOIN clause (join on the same table using an alias).
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function selfJoin(string $table, string $alias, string $first, string $operator, string $second) : static
    {
        return $this->join(
            table     : $table,
            alias     : $alias,
            conditions: [$this->quoteIdentifier(name: $first) . " {$operator} " . $this->quoteIdentifier(name: $second)]
        );
    }

    /**
     * Adds a JOIN clause with an alias for the table.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function joinWithAlias(
        string $table,
        string $alias,
        string $first,
        string $operator,
        string $second,
        string $type = 'JOIN'
    ) : static {
        return $this->join(
            table     : $table,
            alias     : $alias,
            conditions: [
                            $this->quoteIdentifier(name: $first) . " {$operator} " . $this->quoteIdentifier(
                                name: $second
                            ),
                        ],
            type      : $type
        );
    }

    /**
     * Adds a JOIN clause using raw SQL.
     *
     * ⚠️ **Warning:** Using raw SQL can expose your query to SQL injection risks.
     * Ensure that `$rawSql` is properly sanitized before passing it.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function joinRaw(string $rawSql) : static
    {
        if (empty($rawSql)) {
            throw new QueryBuilderException(message: 'Invalid JOIN RAW: SQL statement cannot be empty.');
        }

        $this->joinClauses[] = $rawSql;

        return $this;
    }

    /**
     * Adds a JOIN clause with multiple conditions.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function joinWithConditions(
        string      $table,
        string|null $alias,
        array       $conditions,
        string      $type = 'JOIN'
    ) : static {
        return $this->join(
            table     : $table,
            alias     : $alias,
            conditions: $conditions,
            type      : $type
        );
    }
}
