<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Traits;

use Avax\Database\QueryBuilder\BaseQueryBuilder;
use Avax\Database\QueryBuilder\QueryBuilder;
use InvalidArgumentException;
use PDO;

/**
 * Trait OrderByAndGroupByBuilderTrait
 *
 * Handles SQL ORDER BY, GROUP BY, and HAVING clauses using a fluent interface.
 *
 * ✅ OWASP Security: Prevents SQL Injection via safe query-building practices.
 * ✅ Strict Input Validation: Ensures only valid column names and values are accepted.
 */
trait OrderByAndGroupByBuilderTrait
{
    /**
     * Prefix used for placeholders in query building or other similar operations.
     */
    private const string PLACEHOLDER_PREFIX = 'orderByField_';

    /**
     * Stores ORDER BY clauses.
     */
    private array $orderByClauses = [];

    /**
     * Stores GROUP BY clauses.
     */
    private array $groupByClauses = [];

    /**
     * Stores HAVING clauses.
     */
    private array $havingClauses = [];

    /**
     * Stores bound parameters for safe query execution.
     */
    private array $boundParameters = [];

    /**
     * Resets all ORDER BY, GROUP BY, and HAVING clauses.
     */
    public function resetClauses() : self
    {
        $this->orderByClauses  = [];
        $this->groupByClauses  = [];
        $this->havingClauses   = [];
        $this->boundParameters = [];

        return $this;
    }

    /**
     * Adds an ORDER BY clause.
     *
     */
    public function orderBy(string $column, string $direction = 'ASC') : self
    {
        // Validate the column name to ensure it only contains valid characters (alphanumeric and underscores)
        $this->validateColumnName(name: $column);

        // Convert the order direction (ASC/DESC) to uppercase for consistent comparison
        $direction = strtoupper($direction);

        // Check whether the provided direction is valid (ASC or DESC)
        // If the direction is invalid, throw an exception with an appropriate error message
        if (! in_array($direction, ['ASC', 'DESC'], true)) {
            throw new InvalidArgumentException(message: 'Invalid ORDER BY direction. Use "ASC" or "DESC".');
        }

        // Append the valid ORDER BY clause to the array of clauses
        // The column name is safely enclosed using quoteIdentifier for preventing SQL injection
        $this->orderByClauses[] = sprintf('%s %s', $this->quoteIdentifier(name: $column), $direction);

        // Return the current instance to allow method chaining
        return $this;
    }

    /**
     * Adds an ORDER BY FIELD() clause for custom sorting.
     *
     */
    public function orderByField(string $column, array $values) : self
    {
        // Validate the column name to ensure it contains only alphanumeric characters and underscores
        $this->validateColumnName(name: $column);

        // Check if the input array of values is empty, and throw an exception if it is
        if (empty($values)) {
            throw new InvalidArgumentException(message: 'OrderByField requires a non-empty array of values.');
        }

        // Generate a set of placeholders and their corresponding bindings for the passed values
        $placeholdersWithBindings = $this->generatePlaceholdersWithBindings(values: $values);

        // Add an `ORDER BY FIELD` clause to the list of order clauses
        // The `FIELD` SQL function matches the column value to the provided list of placeholders
        $this->orderByClauses[] = sprintf(
            'FIELD(%s, %s)',
            // Sanitize and properly quote the column name according to the database driver
            $this->quoteIdentifier(name: $column),
            // Create a comma-separated list of placeholders (keys from the bindings array)
            implode(', ', array_keys($placeholdersWithBindings)) // Extract only keys for SQL placeholders
        );

        // Merge the generated parameter bindings with any previously existing bound parameters
        $this->boundParameters = array_merge($this->boundParameters, $placeholdersWithBindings);

        // Return the current instance to allow method chaining
        return $this;
    }

    /**
     * Generates a set of placeholders with corresponding value bindings
     * for use in a prepared SQL statement.
     *
     * @param array $values The array of values to create placeholders for.
     *
     * @return array An associative array where keys are placeholder names
     *               and values are the corresponding data from the input array.
     */
    private function generatePlaceholdersWithBindings(array $values) : array
    {
        // Initialize an array to hold the placeholder-value bindings
        $bindings = [];

        // Loop through each value in the provided array, with its index
        foreach ($values as $index => $value) {
            // Create a unique placeholder name using a prefix and the current index
            $placeholderName = ':' . self::PLACEHOLDER_PREFIX . $index;

            // Map the placeholder name to its corresponding value from the input
            $bindings[$placeholderName] = $value;
        }

        // Return the associative array of placeholders and their corresponding values
        return $bindings;
    }

    /**
     * Adds an ORDER BY RAND() clause for random ordering.
     */
    public function orderByRand() : self
    {
        $this->orderByClauses[] = 'RAND()';

        return $this;
    }

    /**
     * Adds a GROUP BY clause.
     *
     */
    public function groupBy(string $column) : self
    {
        $this->validateColumnName(name: $column);
        $this->groupByClauses[] = $this->quoteIdentifier(name: $column);

        return $this;
    }

    /**
     * Adds a HAVING clause with **secure parameter binding**.
     *
     */
    public function having(string $column, string $operator, mixed $value) : self
    {
        // Validate the column name to ensure it contains only alphanumeric characters and underscores.
        $this->validateColumnName(name: $column);

        // Check if the operator provided is valid by comparing it against the list of allowed operators.
        // If the operator is not valid, throw an InvalidArgumentException.
        if (! in_array($operator, ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'], true)) {
            throw new InvalidArgumentException(message: 'Invalid HAVING operator.');
        }

        // Create a parameter placeholder by replacing periods in the column name with underscores.
        // The placeholder is prefixed with ":having_".
        $placeholder = sprintf(':having_%s', str_replace('.', '_', $column));

        // Build the HAVING clause using the quoted column name, the operator, and the placeholder.
        // Add the resulting clause to the `havingClauses` array.
        $this->havingClauses[] = sprintf('%s %s %s', $this->quoteIdentifier(name: $column), $operator, $placeholder);

        // Store the actual value of the parameter in the `boundParameters` array, keyed by the placeholder.
        // This helps ensure the value is safely bound to the statement later during query execution.
        $this->boundParameters[$placeholder] = $value;

        // Return the current object to allow method chaining.
        return $this;
    }

    /**
     * Modifies the query to sort the results in random order, using the appropriate SQL function based on the database
     * driver.
     *
     * @return BaseQueryBuilder|QueryBuilder|OrderByAndGroupByBuilderTrait Returns applied.
     */
    public function inRandomOrder() : self
    {
        // Retrieve the name of the database driver (e.g., 'mysql', 'pgsql') from the current connection.
        // This is done by accessing the PDO::ATTR_DRIVER_NAME attribute of the PDO connection object.
        $driver = $this->getConnection()->getAttribute(attribute: PDO::ATTR_DRIVER_NAME);

        // Add a clause to the `orderByClauses` array based on the database driver.
        // For PostgreSQL ('pgsql'), use `RANDOM()`; for other database drivers, use `RAND()`.
        $this->orderByClauses[] = match ($driver) {
            'pgsql' => 'RANDOM()', // If the database driver is Postgres, use the `RANDOM()` function for random ordering.
            default => 'RAND()',   // For other database drivers (e.g., MySQL), use the `RAND()` function for random ordering.
        };

        // Return the current object instance to allow method chaining.
        return $this;
    }

    /**
     * Builds the ORDER BY clause.
     */
    public function buildOrderBy() : string
    {
        return empty($this->orderByClauses) ? '' : ' ORDER BY ' . implode(', ', $this->orderByClauses);
    }

    /**
     * Builds the GROUP BY and HAVING clauses.
     */
    public function buildGroupByAndHaving() : string
    {
        $sql = '';

        if (! empty($this->groupByClauses)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupByClauses);
        }

        if (! empty($this->havingClauses)) {
            $sql .= ' HAVING ' . implode(' AND ', $this->havingClauses);
        }

        return $sql;
    }

    /**
     * **Retrieves bound parameters** for safe query execution.
     */
    public function getBoundParameters() : array
    {
        return $this->boundParameters;
    }
}
