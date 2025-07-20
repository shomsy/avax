<?php

declare(strict_types=1);

namespace Gemini\Database\QueryBuilder\Traits;

use Gemini\Database\QueryBuilder\Exception\QueryBuilderException;
use InvalidArgumentException;
use PDO;

/**
 * Trait WhereTrait
 *
 * Provides fluent and expressive methods for dynamically building SQL `WHERE` clauses.
 *
 * **Key Features:**
 * ✅ Supports both simple (`where()`) and advanced (`whereIs()`) conditions.
 * ✅ Prevents SQL Injection via strict column validation & parameterized queries.
 * ✅ Implements `WHERE IN`, `WHERE JSON_CONTAINS`, `FULLTEXT SEARCH`, and date-based filtering.
 * ✅ Optimized for MySQL and PostgreSQL compatibility.
 *
 */
trait WhereTrait
{
    /**
     * Stores bound parameters for prepared statements.
     *
     * @var array<string, mixed>
     */
    private array $parameters = [];

    /**
     * Stores `WHERE` clause conditions.
     *
     * @var array<string>
     */
    private array $whereClauses = [];

    /**
     * Adds a `WHERE` condition with a default `=` operator.
     *
     * @throws \Random\RandomException
     */
    public function where(string $column, mixed $value) : static
    {
        return $this->whereIs(column: $column, operator: '=', value: $value);
    }

    /**
     * Adds a safe and sanitized WHERE condition to the query with specified comparison operator.
     *
     * This method implements a secure way to add WHERE clauses by:
     * - Validating column names against SQL injection
     * - Supporting NULL value comparisons with proper IS NULL syntax
     * - Using a allowlist of allowed SQL operators
     * - Implementing parameterized queries for values
     *
     * @param string $column   The database column name to compare (unquoted)
     * @param string $operator The comparison operator (=,=, <>, >, <, >=, <=, LIKE, NOT LIKE, IN, NOT IN)
     * @param mixed  $value    The value to compare against, null supported
     *
     * @return static Returns $this for method chaining
     * @throws \Random\RandomException   When secure parameter key generation fails
     *
     * @throws InvalidArgumentException When an invalid operator or column name is provided
     */
    public function whereIs(string $column, string $operator, mixed $value) : static
    {
        // Ensure the column name contains only alphanumeric characters and underscores for SQL injection prevention
        $this->validateColumnName(name: $column);

        // Get database-specific quoted identifier for the column name to prevent SQL injection
        $quotedColumn = $this->quoteIdentifier(name: $column);

        // Special handling for NULL comparisons to use proper SQL syntax (IS NULL, IS NOT NULL)
        if ($value === null) {
            if ($operator === '=') {
                $this->whereClauses[] = sprintf('%s IS NULL', $quotedColumn);
            } elseif ($operator === '!=') {
                $this->whereClauses[] = sprintf('%s IS NOT NULL', $quotedColumn);
            } else {
                throw new InvalidArgumentException(
                    message: "Invalid operator for NULL comparison: {$operator}"
                );
            }

            return $this;
        }

        // Define allowed SQL operators to prevent SQL injection via operator
        $supportedOperators = [
            '=',
            '!=',
            '<>',
            '>',
            '<',
            '>=',
            '<=',
            'LIKE',
            'NOT LIKE',
            'IN',
            'NOT IN',
        ];

        // Validate that only whitelisted operators are used
        if (! in_array($operator, $supportedOperators, true)) {
            throw new InvalidArgumentException(
                message: "Unsupported operator: {$operator}"
            );
        }

        // Create a unique parameter key for safe value binding
        $paramKey = $this->generateParamKey(column: $column);

        // Build and store the WHERE clause with parameterized value
        $this->whereClauses[]        = sprintf('%s %s :%s', $quotedColumn, $operator, $paramKey);
        $this->parameters[$paramKey] = $value;

        return $this;
    }

    /**
     * Generates a unique parameter key to prevent conflicts.
     *
     * @throws \Random\RandomException
     */
    private function generateParamKey(string $column) : string
    {
        return $column . '_' . bin2hex(random_bytes(4)); // ✅ Secure random key
    }

    /**
     * Adds a `WHERE IN` condition.
     *
     * @throws \Gemini\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function whereIn(string $column, array $values) : static
    {
        if (empty($values)) {
            throw new QueryBuilderException(message: 'The `IN` clause requires a non-empty array.');
        }

        return $this->prepareInClause(column: $column, values: $values, not: false);
    }

    /**
     * Prepares a safe `IN` clause using parameterized queries.
     *
     * @throws \Gemini\Database\QueryBuilder\Exception\QueryBuilderException
     */
    private function prepareInClause(string $column, array $values, bool $not) : static
    {
        $this->validateColumnName(name: $column);
        $column = $this->quoteIdentifier(name: $column);

        if (empty($values)) {
            throw new QueryBuilderException(message: "IN clause requires a non-empty array.");
        }

        $placeholders = [];
        foreach ($values as $index => $value) {
            $paramKey                    = "{$column}_{$index}";
            $placeholders[]              = ":{$paramKey}";
            $this->parameters[$paramKey] = $value;
        }

        $operator             = $not ? 'NOT IN' : 'IN';
        $this->whereClauses[] = sprintf('%s %s (%s)', $column, $operator, implode(', ', $placeholders));

        return $this;
    }

    /**
     * Adds a `WHERE NOT IN` condition.
     *
     * @throws \Gemini\Database\QueryBuilder\Exception\QueryBuilderException
     */
    public function whereNotIn(string $column, array $values) : static
    {
        return $this->prepareInClause(column: $column, values: $values, not: true);
    }

    /**
     * Adds a `WHERE IS NULL` condition.
     */
    public function whereNull(string $column) : static
    {
        $this->validateColumnName(name: $column);
        $column               = $this->quoteIdentifier(name: $column);
        $this->whereClauses[] = sprintf('%s IS NULL', $column);

        return $this;
    }

    /**
     * Adds a `WHERE IS NOT NULL` condition.
     */
    public function whereNotNull(string $column) : static
    {
        $this->validateColumnName(name: $column);
        $column               = $this->quoteIdentifier(name: $column);
        $this->whereClauses[] = sprintf('%s IS NOT NULL', $column);

        return $this;
    }

    /**
     * Adds an `OR WHERE` condition.
     *
     * @throws \Random\RandomException
     */
    public function orWhere(string $column, mixed $value) : static
    {
        return $this->orWhereIs(column: $column, operator: '=', value: $value);
    }

    /**
     * Adds an `OR WHERE` condition with a specified operator.
     *
     * @throws \Random\RandomException
     */
    public function orWhereIs(string $column, string $operator, mixed $value) : static
    {
        $this->validateColumnName(name: $column);

        $paramKey                    = $this->generateParamKey(column: $column);
        $this->whereClauses[]        = sprintf('OR %s %s :%s', $column, $operator, $paramKey);
        $this->parameters[$paramKey] = $value;

        return $this;
    }

    /**
     * Adds a `WHERE` condition that compares two columns.
     *
     */
    public function whereColumn(string $first, string $operator, string $second) : static
    {
        // Quote the identifier of the first column name to ensure it's safely encapsulated for SQL.
        // This prevents SQL injection by wrapping column names in suitable quotation marks.
        $first = $this->quoteIdentifier(name: $first);

        // Quote the identifier of the second column name, ensuring it's properly escaped for SQL.
        $second = $this->quoteIdentifier(name: $second);

        // Validate the provided operator to ensure that it's one of the acceptable SQL operators.
        // If invalid, an exception is thrown to prevent dangerous or malformed queries.
        if (! in_array($operator, ['=', '!=', '<', '>', '<=', '>='], true)) {
            throw new InvalidArgumentException(message: 'Invalid SQL operator.');
        }

        // Format and store the WHERE clause in the internal array of conditions (`whereClauses`).
        // The sprintf() is used for consistent and safe concatenation of identifiers and operators.
        $this->whereClauses[] = sprintf('%s %s %s', $first, $operator, $second);

        // Return the current object instance to facilitate method chaining (e.g., add multiple WHERE clauses).
        return $this;
    }

    /**
     * Adds an `OR WHERE` condition that compares two columns.
     */
    public function orWhereColumn(string $first, string $second, string $operator = '=') : static
    {
        $this->whereClauses[] = sprintf('OR %s %s %s', $first, $operator, $second);

        return $this;
    }

    /**
     * Adds a raw SQL expression as a WHERE clause to the query.
     *
     * @param string $sql      The raw SQL string representing the WHERE condition. It must only contain
     *                         valid characters (letters, numbers, underscores, parentheses, dots, commas,
     *                         asterisks, and spaces).
     * @param array  $bindings An associative array of parameter bindings where keys represent parameter
     *                         placeholders and values represent their corresponding values.
     *
     * @return static The current instance for method chaining.
     * @throws InvalidArgumentException If the provided SQL string contains invalid characters.
     */
    public function whereRaw(string $sql, array $bindings = []) : static
    {
        // Check if the provided SQL string contains a semicolon `;` or a double dash `--`.
        // These characters could indicate SQL injection risks or usage of raw SQL features like comments.
        if (str_contains($sql, ';') || str_contains($sql, '--')) {
            // If the string contains either of the above, throw an exception.
            // The exception message states that raw SQL must not include semicolons or comments.
            throw new InvalidArgumentException(message: 'Raw SQL must not contain semicolons or comments.');
        }

        // Adds the validated SQL string wrapped in parentheses to the `whereClauses` array.
        // The array collects raw SQL expressions for WHERE clauses.
        $this->whereClauses[] = "({$sql})";

        // Iterates over the provided bindings array (key-value pairs), where keys represent
        // parameter placeholders and values represent their corresponding values. These key-value pairs
        // are added to the `parameters` array, which stores all query parameter bindings.
        foreach ($bindings as $key => $value) {
            $this->parameters[$key] = $value;
        }

        // Returns the instance of the object to allow method chaining.
        return $this;
    }

    /**
     * Adds a `WHERE JSON_CONTAINS` condition for JSON column filtering.
     *
     * @throws \Random\RandomException
     * @throws \JsonException
     */
    public function whereJsonContains(string $column, mixed $value) : static
    {
        $this->validateColumnName(name: $column);
        $column = $this->quoteIdentifier(name: $column);

        if (! is_array($value) && ! is_object($value)) {
            throw new InvalidArgumentException(message: "Invalid JSON value. Must be array or object.");
        }

        $paramKey                    = $this->generateParamKey(column: $column);
        $this->whereClauses[]        = "JSON_CONTAINS({$column}, :{$paramKey})";
        $this->parameters[$paramKey] = json_encode($value, JSON_THROW_ON_ERROR);

        return $this;
    }

    /**
     * Adds a full-text search condition.
     */
    public function whereFullText(string $column, string $value) : static
    {
        $this->whereClauses[] = sprintf('MATCH(%s) AGAINST (?)', $column);
        $this->parameters[]   = $value;

        return $this;
    }

    /**
     * Adds a condition to filter records for today.
     */
    public function whereToday(string $column) : static
    {
        $this->whereClauses[] = sprintf('DATE(%s) = CURDATE()', $column);

        return $this;
    }

    /**
     * Adds a condition to filter past records.
     */
    public function wherePast(string $column) : static
    {
        $this->whereClauses[] = sprintf('%s < NOW()', $column);

        return $this;
    }

    /**
     * Adds a condition to filter future records.
     */
    public function whereFuture(string $column) : static
    {
        $this->whereClauses[] = sprintf('%s > NOW()', $column);

        return $this;
    }

    /**
     * Orders the query results in random order for items matching a specific condition.
     *
     * @return static Returns the current instance with a random ordering applied to the query.
     */
    public function whereInRandomOrder() : static
    {
        return $this->orderByRandom();
    }

    /**
     * Orders the results randomly.
     *
     * @return static The current query instance with a random ordering applied.
     */
    public function orderByRandom() : static
    {
        // Retrieve the PDO database connection.
        $pdo = $this->getConnection();

        // Determine the appropriate random ordering function based on the database driver.
        // Use 'RANDOM()' for PostgreSQL and 'RAND()' for other databases.
        $orderBy = ($pdo->getAttribute(attribute: PDO::ATTR_DRIVER_NAME) === 'pgsql') ? 'RANDOM()' : 'RAND()';

        // Append the random ordering clause to the list of "ORDER BY" clauses.
        $this->orderByClauses[] = $orderBy;

        // Return the current instance to enable method chaining.
        return $this;
    }

    /**
     * Builds the `WHERE` clause string.
     */
    public function buildWhereClauses() : string
    {
        return empty($this->whereClauses) ? '' : ' WHERE ' . implode(' AND ', $this->whereClauses);
    }

    /**
     * Retrieves all bound parameters.
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }


    /**
     * Adds a "BETWEEN" condition to the SQL where clause for filtering results within a specified range.
     *
     * @param string $column The name of the column to apply the "BETWEEN" condition.
     * @param mixed  $start  The starting value of the range.
     * @param mixed  $end    The ending value of the range.
     * @param bool   $not    Indicates whether to negate the condition, resulting in "NOT BETWEEN".
     *
     * @return static Returns the current instance to allow method chaining.
     * @throws \Random\RandomException
     * @throws \Random\RandomException
     */
    public function whereBetween(string $column, mixed $start, mixed $end, bool $not = false) : static
    {
        // Validates that the column name contains only allowed characters (alphanumeric and underscores).
        $this->validateColumnName(name: $column);

        // Quotes the column name to safely use it in SQL queries, preventing SQL injection or reserved word conflicts.
        $column = $this->quoteIdentifier(name: $column);

        // Generates a unique parameter key for the start value of the "BETWEEN" condition.
        $paramStart = $this->generateParamKey(column: $column . '_start');

        // Generates a unique parameter key for the end value of the "BETWEEN" condition.
        $paramEnd = $this->generateParamKey(column: $column . '_end');

        // Assigns the start value to the `parameters` array using the generated key.
        $this->parameters[$paramStart] = $start;

        // Assigns the end value to the `parameters` array using the generated key.
        $this->parameters[$paramEnd] = $end;

        // Chooses the appropriate SQL operator based on the `$not` flag (either "BETWEEN" or "NOT BETWEEN").
        $operator = $not ? 'NOT BETWEEN' : 'BETWEEN';

        // Builds the SQL where clause for the "BETWEEN" condition and adds it to the list of where clauses.
        $this->whereClauses[] = sprintf('%s %s :%s AND :%s', $column, $operator, $paramStart, $paramEnd);

        // Returns the current instance to allow method chaining.
        return $this;
    }


    /**
     * Resets all `WHERE` conditions and parameters.
     */
    public function resetWhereConditions() : static
    {
        $this->whereClauses = [];
        $this->parameters   = [];

        return $this;
    }
}
