<?php

declare(strict_types=1);

namespace Gemini\Database\QueryBuilder\Traits;

use Gemini\Database\QueryBuilder\Enums\QueryBuilderEnum;
use Gemini\Database\QueryBuilder\Exception\QueryBuilderException;
use PDO;

/**
 * **InsertUpdateTrait**
 *
 * Provides transactional `INSERT`, `UPDATE`, `BATCH INSERT`, and `UPSERT` operations,
 * while integrating a **Unit of Work** mechanism to delay execution until explicitly flushed.
 *
 * **Security Enhancements:**
 * - 🛡️ **Prevents SQL Injection** with strict parameter binding.
 * - 🔒 **Ensures transactional integrity** for batch operations.
 * - 🚀 **Optimized for large datasets** (batch inserts split into smaller transactions).
 */
trait InsertUpdateTrait
{
    /**
     * Inserts multiple rows of data into the database in batches.
     *
     * This method takes an array of rows, splits them into smaller chunks, and executes
     * batch insert queries to optimize a database writes. It uses parameterized queries
     * to prevent SQL injection and works on tables with the structure defined by the
     * QueryBuilder instance.
     *
     * @param array $rows An array where each element is an associative array representing a row
     *                    to be inserted. Each row must contain the same keys, which correspond
     *                    to the column names in the database table.
     *
     * @return static Returns the current instance of the QueryBuilder to enable method chaining.
     *
     * @throws QueryBuilderException Throws an exception if the input array of rows is empty.
     */
    public function batchInsert(array $rows) : static
    {
        // Check if the input array of rows is empty
        if (empty($rows)) {
            // Throw a custom exception if no data is provided for the batch insert
            throw new QueryBuilderException(message: 'No data provided for batch insert.');
        }

        // Get the database connection instance (PDO)
        $pdo = $this->getConnection();
        // Disable emulation of prepared statements to improve security and prevent SQL injection
        $pdo->setAttribute(attribute: PDO::ATTR_EMULATE_PREPARES, value: false); // 🛡️ Prevents SQL Injection

        // Extract the column names from the first row of the input data
        $columns = array_keys($rows[0]);
        // Create a comma-separated list of column names for the SQL statement
        $columnsList = implode(', ', $columns);

        // Split the input data into smaller chunks, with each chunk containing up to 500 rows
        $chunks = array_chunk($rows, 500); // ✅ Splits into batches of 500 rows

        // Iterate over each chunk of data
        foreach ($chunks as $chunk) {
            // Initialize an array to store SQL placeholders for the values
            $placeholders = [];
            // Initialize an array to store the query parameters
            $parameters = [];

            // Iterate over each row in the current chunk
            foreach ($chunk as $index => $row) {
                // Generate placeholders for the current row's values using the column names and row index
                $rowPlaceholders = array_map(static fn($key) => ":{$key}_{$index}", $columns);
                // Combine the placeholders into a parenthesized string and add to the placeholders array
                $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
                // Map the row values to their corresponding placeholders
                foreach ($row as $key => $value) {
                    $parameters["{$key}_{$index}"] = $value;
                }
            }

            // Generate the SQL query for inserting the current chunk of data
            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES %s',
                $this->getTableName(),      // Get the table name from the QueryBuilder instance
                $columnsList,              // The list of columns to insert data into
                implode(', ', $placeholders) // Comma-separated list of placeholders for all rows
            );

            // Prepare the SQL statement to be executed
            $statement = $pdo->prepare(query: $sql);
            // Execute the prepared statement with the mapped parameters
            $this->registerQueryInUnitOfWork(
                operation : QueryBuilderEnum::QUERY_TYPE_INSERT,
                statement : $statement,
                pdo       : $pdo,
                parameters: $parameters
            );
        }

        // Return the current QueryBuilder instance to support method chaining
        return $this;
    }

    /**
     * Performs an upsert operation, inserting a record if it does not exist,
     * or updating the specified columns if a duplicate key is found.
     *
     * The method supports MySQL's `ON DUPLICATE KEY UPDATE` or PostgreSQL's
     * `ON CONFLICT DO UPDATE` based on the database driver.
     * Uses parameterized queries to enhance security and **prevent SQL injection**.
     *
     * @param array $values        The dataset to be inserted. Keys are column names and values are their respective
     *                             values.
     * @param array $updateColumns The column names to be updated in case of a duplicate key or conflict.
     *
     * @return static The current instance for method chaining.
     * @throws QueryBuilderException If no data is provided or if required arrays are empty.
     *
     */
    public function upsert(array $values, array $updateColumns) : static
    {
        // Check if the `$values` array or `$updateColumns` array is empty.
        // If either is empty, throw a custom `QueryBuilderException` since there is no data to perform an upsert operation.
        if (empty($values) || empty($updateColumns)) {
            throw new QueryBuilderException(message: 'No data provided for upsert.');
        }

        // Retrieve the PDO database connection using the `getConnection` method.
        $pdo = $this->getConnection();

        // Set the PDO attribute to disable emulated prepared statements.
        // This improves security by preventing SQL injection attacks.
        $pdo->setAttribute(attribute: PDO::ATTR_EMULATE_PREPARES, value: false); // 🛡️ Prevents SQL Injection

        // Create a comma-separated string of column names from the keys of the `$values` array.
        $columns = implode(', ', array_keys($values));

        // Create a comma-separated string of placeholders (e.g., `:column_name`) for prepared statements.
        $placeholders = implode(', ', array_map(static fn($key) => ":{$key}", array_keys($values)));

        // Create a comma-separated string of `column = :update_column` pairs for the ON DUPLICATE KEY UPDATE clause.
        $updates = implode(', ', array_map(static fn($col) => "{$col} = :update_{$col}", $updateColumns));

        // MySQL uses "ON DUPLICATE KEY UPDATE", while PostgreSQL uses "ON CONFLICT (...) DO UPDATE".
        // The query is dynamically adjusted based on the database driver.
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $this->getTableName(), // Get the table name from the class property or throw if not set.
            $columns,              // Columns to insert data into.
            $placeholders,         // Placeholders for prepared statement values.
            $updates               // Update statement for duplicate key cases.
        );

        // Check if the current database driver is PostgreSQL using the PDO driver name.
        if ($pdo->getAttribute(attribute: PDO::ATTR_DRIVER_NAME) === QueryBuilderEnum::DRIVER_PGSQL->value) {
            // Create a comma-separated string of columns used in PostgreSQL's ON CONFLICT clause.
            $conflictColumns = implode(', ', $updateColumns);
            // Construct the SQL query string for PostgreSQL's INSERT INTO ... ON CONFLICT ... DO UPDATE statement.
            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES (%s) ON CONFLICT (%s) DO UPDATE SET %s',
                $this->getTableName(),  // Get the table name from the class property or throw if not set.
                $columns,               // Columns to insert data into.
                $placeholders,          // Placeholders for prepared statement values.
                $conflictColumns,       // Columns to check for conflicts.
                $updates                // Update statement for conflict cases.
            );
        }

        // Prepare the SQL statement using the PDO `prepare` method.
        // This step ensures the query is safe to execute and supports parameterized values for security.
        $statement = $pdo->prepare(query: $sql);

        // ❗ This registers the query with the Unit of Work system, so it will be executed in a controlled batch during flush().
        // The type of operation is specified as an enum value representing the "INSERT" query type.
        $this->registerQueryInUnitOfWork(
            operation : QueryBuilderEnum::QUERY_TYPE_INSERT,
            statement : $statement,
            pdo       : $pdo,
            parameters: $values // The array of parameters to bind to the statement for execution.
        );

        // Return the current object instance, allowing method chaining.
        return $this;
    }

    /**
     * Inserts a single row into the database.
     *
     * Uses a prepared statement with parameterized queries to prevent SQL injection.
     *
     * @param array $parameters The key-value pairs representing column names and their respective values to be
     *                          inserted.
     *
     * @return static Returns the current instance for method chaining after a successful insert.
     *
     * @throws QueryBuilderException If no data is provided for the insert operation.
     */
    public function insert(array $parameters) : static
    {
        // Check if the provided parameters are empty, throw exception if true
        if (empty($parameters)) {
            throw new QueryBuilderException(message: 'No data provided for insert.');
        }

        // Retrieve the PDO database connection object
        $pdo = $this->getConnection();

        // Disable PDO's emulated prepared statements to prevent SQL Injection
        $pdo->setAttribute(attribute: PDO::ATTR_EMULATE_PREPARES, value: false); // 🛡️ Prevents SQL Injection

        // Create a comma-separated list of column names from the parameter keys
        $columns = implode(', ', array_keys($parameters));

        // Create a comma-separated list of named placeholders corresponding to the parameter keys
        $placeholders = implode(', ', array_map(static fn($key) => ":{$key}", array_keys($parameters)));

        // Build the SQL query for inserting data into the table
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->getTableName(), $columns, $placeholders);

        // Prepare the SQL query using the PDO connection
        $statement = $pdo->prepare(query: $sql);

        // Register the query in the unit of work for consistency and potential deferred execution
        $this->registerQueryInUnitOfWork(
            operation : QueryBuilderEnum::QUERY_TYPE_INSERT,
            statement : $statement,
            pdo       : $pdo,
            parameters: $parameters
        );

        // Return the current instance for method chaining
        return $this;
    }

    /**
     * Performs an update operation on records that match the specified conditions.
     *
     * Uses parameterized queries to **prevent SQL injection**. Both the updated data
     * and the conditions must be provided to ensure a valid operation.
     *
     * @param array $values     The data to update with column-value pairs.
     * @param array $conditions The conditions to determine which records to update.
     *
     * @return static Returns the current instance for method chaining.
     * @throws QueryBuilderException If no data or conditions are provided.
     *
     */
    public function update(array $values, array $conditions) : static
    {
        // Check if either the update data ($values) or the conditions ($conditions) are empty.
        // If either is empty, throw a QueryBuilderException to ensure both are provided.
        if (empty($values) || empty($conditions)) {
            throw new QueryBuilderException(message: 'No data or conditions provided for update.');
        }

        // Obtain the database connection using the `getConnection` method.
        // This ensures we have access to the database with a valid PDO instance.
        $pdo = $this->getConnection();

        // Set the PDO attribute to disable emulated prepared statements.
        // 🛡️ This strengthens security by preventing SQL injection attacks.
        $pdo->setAttribute(attribute: PDO::ATTR_EMULATE_PREPARES, value: false);

        // Use array mapping to construct the `SET` portion of the SQL statement.
        // Each `SET` clause follows the format "column = :set_column".
        $setClauses = implode(', ', array_map(static fn($col) => "{$col} = :set_{$col}", array_keys($values)));

        // Use array mapping to construct the `WHERE` portion of the SQL statement.
        // Each condition in `WHERE` follows the format "column = :where_column".
        $whereClauses = implode(
            ' AND ',
            array_map(static fn($col) => "{$col} = :where_{$col}", array_keys($conditions))
        );

        // Create the final SQL query string using the table name, `SET` clauses, and `WHERE` clauses.
        // This forms a valid SQL UPDATE query.
        $sql = sprintf('UPDATE %s SET %s WHERE %s', $this->getTableName(), $setClauses, $whereClauses);

        // Prepare the SQL statement using the PDO instance.
        // This allows binding parameters securely before executing the query.
        $statement = $pdo->prepare(query: $sql);

        // Initialize an empty array to hold all parameters for the prepared statement.
        $parameters = [];

        // Populate the $parameters array for the `SET` part of the SQL query.
        // Prefix each key in $values with "set_" to match the placeholders in the query.
        foreach ($values as $key => $value) {
            $parameters["set_{$key}"] = $value;
        }

        // Populate the $parameters array for the `WHERE` part of the SQL query.
        // Prefix each key in $conditions with "where_" to match the placeholders in the query.
        foreach ($conditions as $key => $value) {
            $parameters["where_{$key}"] = $value;
        }

        // Registers a query with the Unit of Work, specifying it as an UPDATE operation.
        // The `QueryBuilderEnum::QUERY_TYPE_UPDATE` indicates the type of a query being performed.
        $this->registerQueryInUnitOfWork(
            operation : QueryBuilderEnum::QUERY_TYPE_UPDATE, // Specifies the type of query as an 'UPDATE' operation.
            statement : $statement, // Passes the prepared PDO statement to be executed.
            pdo       : $pdo,  // Passes PDO connection
            parameters: $parameters // Provides the parameters for the query, likely used for a prepared statement binding.
        );

        // Return the current instance, allowing method chaining if needed.
        return $this;
    }
}
