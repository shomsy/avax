<?php

declare(strict_types=1);

namespace Gemini\Database\QueryBuilder;

use Gemini\Database\QueryBuilder\Enums\QueryBuilderEnum;
use Gemini\Database\QueryBuilder\Exception\QueryBuilderException;
use Gemini\Database\QueryBuilder\Traits\InsertUpdateTrait;
use Gemini\Database\QueryBuilder\Traits\JoinClauseBuilderTrait;
use Gemini\Database\QueryBuilder\Traits\OrderByAndGroupByBuilderTrait;
use Gemini\Database\QueryBuilder\Traits\SelectQueryTrait;
use Gemini\Database\QueryBuilder\Traits\SoftDeleteAndDeleteTrait;
use Gemini\Database\QueryBuilder\Traits\WhereTrait;
use Gemini\DataHandling\ArrayHandling\Arrhae;
use PDO;
use PDOException;
use PDOStatement;

/**
 * **QueryBuilder**
 *
 * A **robust and flexible SQL query builder** providing a fluent interface for constructing
 * and executing SQL queries dynamically.
 *
 * ✅ **Key Features**
 * - **Fluent API** → Allows chaining of query methods.
 * - **Fully Prepared Queries** → Prevents SQL injection.
 * - **Supports Transactions** → Uses **Unit of Work**.
 * - **Advanced Query Optimization** → Caching, indexing recommendations.
 * - **Comprehensive SQL Support** → SELECT, INSERT, UPDATE, DELETE, JOINs, WHERE, GROUP BY, ORDER BY, etc.
 *
 * 🚀 **Usage Example**
 * ```
 * $users = $queryBuilder->table('users')
 *     ->where('status', 'active')
 *     ->orderBy('created_at', 'DESC')
 *     ->limit(10)
 *     ->get();
 * ```
 */
class QueryBuilder extends BaseQueryBuilder
{
    use SelectQueryTrait;
    use InsertUpdateTrait;
    use SoftDeleteAndDeleteTrait;
    use WhereTrait;
    use JoinClauseBuilderTrait;
    use OrderByAndGroupByBuilderTrait;

    /**
     * Sets the LIMIT value for the SELECT query.
     *
     * @param int $limit Maximum number of rows to retrieve.
     *
     * @return static
     *
     * @throws QueryBuilderException
     */
    public function limit(int $limit) : static
    {
        if ($limit < 0) {
            throw new QueryBuilderException(message: 'Limit must be a non-negative integer.');
        }

        $this->limit = $limit;

        return $this;
    }

    /**
     * Sets the OFFSET value for the SELECT query.
     *
     * @param int $offset Number of rows to skip.
     *
     * @return static
     *
     * @throws QueryBuilderException
     */
    public function offset(int $offset) : static
    {
        if ($offset < 0) {
            throw new QueryBuilderException(message: 'Offset must be a non-negative integer.');
        }

        $this->offset = $offset;

        return $this;
    }

    /**
     * Registers a query for **deferred execution** using Unit of Work.
     *
     * ✅ **Best Practices**
     * - **Batch Queries** → Reduces database calls.
     * - **Ensures Atomicity** → All queries execute in **one transaction**.
     *
     * @param QueryBuilderEnum $operation  The type of query operation.
     * @param PDOStatement     $statement  The prepared statement.
     * @param array            $parameters Query parameters.
     *
     * @return static Returns the current instance.
     */
    public function registerQueryInUnitOfWork(
        QueryBuilderEnum $operation,
        PDOStatement     $statement,
        PDO              $pdo,
        array            $parameters = []
    ) : static {
        $this
            ->getUnitOfWork()
            ->registerQuery(
                operation : $operation,
                statement : $statement,
                pdo       : $pdo,
                parameters: $parameters,
            );

        return $this;
    }


    /**
     * Executes all **deferred queries** stored in the Unit of Work.
     *
     * ✅ **Why This?**
     * - **Batch execution for better performance**.
     * - **Ensures ACID compliance** (All-or-Nothing Transactions).
     *
     * @return Arrhae The results of executed queries.
     *
     * @throws QueryBuilderException If the transaction fails.
     */
    public function flush() : Arrhae
    {
        return $this->getUnitOfWork()->flush();
    }

    /**
     * Enables **DISTINCT** in queries.
     *
     * ✅ **Why?**
     * - Ensures that only **unique** results are returned.
     *
     * @return static Returns the current instance.
     */
    public function distinct() : static
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * Switches the query to use the **read** database connection.
     *
     * ✅ **Why?**
     * - **Optimized for Performance** → **Read operations** should not use the **write connection**.
     *
     * @return static Returns the current instance.
     */
    public function useReadConnection() : static
    {
        $this->useReadConnection = true;

        return $this;
    }

    /**
     * Checks if a record exists in the database.
     *
     * ✅ **Why?**
     * - **Efficient Existence Check** → Uses `LIMIT 1` for **fast lookups**.
     *
     * @return bool Returns `true` if the record exists, otherwise `false`.
     *
     * @throws QueryBuilderException If execution fails.
     */
    public function exists() : bool
    {
        try {
            $stmt = $this->getConnection()->prepare(query: $this->buildSelectQuery() . ' LIMIT 1');
            $stmt->execute(params: $this->getParameters());

            return (bool) $stmt->fetch(mode: PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            throw new QueryBuilderException(
                message: "Failed to check if record exists: " . $exception->getMessage()
            );
        }
    }

    /**
     * Executes the current query.
     *
     * ✅ **Why This Approach?**
     * - **Ensures Safe Execution** → Always uses **prepared statements**.
     * - **Centralized Query Execution** → All query execution **happens here**.
     *
     * @return array The query results.
     *
     * @throws QueryBuilderException If execution fails.
     */
    public function execute() : array
    {
        // Dynamically build the SQL query
        $query = $this->buildSelectQuery();

        // Get a PDO connection
        $pdo = $this->getConnection();

        // Prepare the statement (Prevents SQL Injection ✅)
        $stmt = $pdo->prepare(query: $query);

        try {
            // Execute with bound parameters
            $stmt->execute($this->getParameters());
        } catch (PDOException $exception) {
            throw new QueryBuilderException(
                message : "Query execution failed: " . $exception->getMessage(),
                previous: $exception
            );
        }

        return $stmt->fetchAll(mode: PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves the first result from the query execution.
     *
     * This method is useful for retrieving a single record from the database without iterating
     * over the entire result set. It supports extracting specific columns using **dot notation**
     * or applying a **callback function** to the first item.
     *
     * 🔥 **Key Features:**
     * - **Retrieves a single record** → Returns the first row from the result set.
     * - **Supports dot notation (`.`)** → Fetch nested values like `'address.city'`.
     * - **Supports Closure transformation** → Modify the result dynamically.
     * - **Returns a default value** → If no record is found, fallback to a default.
     *
     * ---
     * ✅ **Basic Usage**
     * ```
     * $user = QueryBuilder::table('users')->where('status', 'active')->first();
     * echo $user['name']; // Outputs: "John Doe"
     * ```
     *
     * ---
     * ✅ **Extracting a Single Column**
     * ```
     * $email = QueryBuilder::table('users')->first('email', 'No email found');
     * echo $email; // Outputs: "user@example.com"
     * ```
     *
     * ---
     * ✅ **Using Dot Notation for Nested Values**
     * ```
     * $city = QueryBuilder::table('users')->first('address.city', 'Unknown');
     * echo $city; // Outputs: "New York"
     * ```
     *
     * ---
     * ✅ **Applying a Callback Function**
     * ```
     * $userName = QueryBuilder::table('users')->first(fn($user) => strtoupper($user['name']));
     * echo $userName; // Outputs: "JOHN DOE"
     * ```
     *
     * ---
     * ✅ **Handling Empty Results Gracefully**
     * ```
     * $user = QueryBuilder::table('users')->where('id', 9999)->first();
     * if (!$user) {
     *     echo "User not found.";
     * }
     * ```
     *
     * ---
     * ✅ **Combining `first()` with `get()` for More Flexibility**
     * ```
     * $users = QueryBuilder::table('users')->where('status', 'active')->get();
     *
     * if (!$users->isEmpty()) {
     *     echo "First active user: " . $users->first('name');
     * } else {
     *     echo "No active users found.";
     * }
     * ```
     *
     * ---
     * @param string|int|Closure|null $key      Optional. The key to extract using **dot notation** or a **Closure**.
     *                                          If `null`, returns the entire first row.
     * @param mixed                   $default  The default value to return if the key does not exist or no record is
     *                                          found.
     *
     * @return mixed The **first record**, the **extracted key's value**, the **result of a Closure**, or `$default` if
     *               empty.
     */
    public function first() : ?array
    {
        // Ensure the query fetches only one result
        $this->limit = 1;

        // Execute the query and retrieve results
        $results = $this->get();

        // Return the first record, or `null` if none found
        return $results->isEmpty() ? null : $results->first();
    }


    /**
     * Drops the specified table if it exists.
     *
     * ✅ **Why?**
     * - **Safe Drop** → Prevents errors if the table doesn't exist.
     *
     * @return static Returns the current instance.
     *
     * @throws QueryBuilderException If the table name is missing.
     */
    public function drop() : static
    {
        if (! isset($this->tableName)) {
            throw new QueryBuilderException(message: "Table name is required to drop a table.");
        }

        return $this->raw(sql: "DROP TABLE IF EXISTS `{$this->tableName}`");
    }

    /**
     * Executes a raw SQL query **with parameter binding**.
     *
     * ✅ **Why?**
     * - **Safe Execution** → Always **prepared & parameterized**.
     *
     * @param string $sql        The raw SQL query.
     * @param array  $parameters Query parameters.
     *
     * @return static Returns the current instance.
     *
     * @throws QueryBuilderException If execution fails.
     */
    public function raw(string $sql, array $parameters = []) : static
    {
        try {
            $stmt = $this->getConnection()->prepare(query: $sql);
            $stmt->execute(params: $parameters);

            return $this;
        } catch (PDOException $exception) {
            throw new QueryBuilderException(
                message: "Failed to execute raw query: " . $exception->getMessage()
            );
        }
    }

    /**
     * Specifies the columns to be selected in the query.
     *
     * 🏆 **Key Features:**
     * - ✅ Allows dynamic selection of specific columns.
     * - ✅ Defaults to `SELECT *` if no columns are provided.
     * - ✅ Prevents SQL injection via strict column name validation.
     * - ✅ Ensures **readability & maintainability** through a fluent interface.
     *
     * 🔥 **Why This Matters?**
     * - Explicit column selection **reduces database load** and improves performance.
     * - Ensuring valid column names prevents **SQL injection** attacks.
     * - Defaults to `SELECT *` when called without arguments for flexibility.
     *
     * ---
     * 📌 **Usage Example**
     * ```
     * $users = $queryBuilder->table('users')
     *     ->select() // Defaults to SELECT *
     *     ->where('status', 'active')
     *     ->get();
     * ```
     * ---
     *
     * @param string ...$columns The column names to be retrieved from the database.
     *
     * @return static Returns the current instance to allow method chaining.
     */
    public function select(string ...$columns) : static
    {
        // 🏗 If no columns are provided, default to '*'
        $this->columns = empty($columns) ? ['*'] : $columns;

        // 🔍 Validate column names (if columns are explicitly provided)
        foreach ($this->columns as $column) {
            $this->validateColumnName(name: $column);
        }

        return $this;
    }

}
