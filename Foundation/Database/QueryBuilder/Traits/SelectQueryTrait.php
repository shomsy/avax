<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Traits;

use Avax\Database\QueryBuilder\Enums\QueryBuilderEnum;
use Avax\Database\QueryBuilder\Exception\QueryBuilderException;
use Avax\DataHandling\ArrayHandling\Arrhae;
use JsonException;
use PDO;
use PDOException;
use PDOStatement;
use Psr\SimpleCache\CacheInterface;

/**
 * Trait SelectQueryTrait
 *
 * Provides functionality for handling SELECT queries, including:
 * - 🔥 Built-in caching (In-Memory + External Cache)
 * - 🚀 Query performance optimization
 * - 📌 Pagination & indexing
 * - 💾 Hybrid cache system with cache invalidation
 */
trait SelectQueryTrait
{
    /**
     * Controls whether the SELECT query should return distinct results.
     *
     * @readonly
     * @var bool Defaults to false for standard SELECT operations
     */
    protected bool $distinct = false;

    /**
     * Specifies the window function to be applied in the query.
     * Used for analytical operations like ROW_NUMBER(), RANK(), etc.
     *
     * @readonly
     * @var string|null Window function SQL expression or null if not used
     */
    protected string|null $windowFunction = null;

    /**
     * Defines the columns to be retrieved in the SELECT statement.
     * Supports both array of column names and complex expressions.*
     */
    protected array $columns = [];

    /**
     * Determines the locking strategy for the SELECT operation.
     * Supports pessimistic/optimistic locking mechanisms.
     *
     * @readonly
     * @var string|null Lock mode (e.g., 'FOR UPDATE', 'SHARED') or null for no explicit locking
     */
    protected string|null $lockMode = null;

    /**
     * Maximum number of rows to return in the result set.
     * Implements pagination control alongside offset.
     *
     * @readonly
     * @var positive-int|null Number of rows to limit or null for no limit
     */
    protected int|null $limit = null;

    /**
     * Number of rows to skip before starting to return rows.
     * Used in conjunction with the limit for pagination implementation.
     *
     * @readonly
     * @var non-negative-int|null Number of rows to offset or null for no offset
     */
    protected int|null $offset = null;

    /**
     * Determines if query results should be cached.
     * Enables performance optimization for frequently accessed data.
     *
     * @readonly
     * @var bool Defaults to false for real-time query execution
     */
    protected bool $cacheEnabled = false;

    /**
     * Duration in seconds for which cached results remain valid.
     * Affects cache invalidation strategy.
     *
     * @readonly
     * @var positive-int Cache time-to-live in seconds, defaults to 300 (5 minutes)
     */
    protected int $cacheTTL = 300;

    /**
     * Cache implementation for storing query results.
     * Supports PSR-16 compatible cache interfaces.
     *
     * @readonly
     * @var CacheInterface|null Cache implementation or null if caching is disabled
     */
    protected CacheInterface|null $cache = null;

    /**
     * Optional index hint for query optimization.
     * Allows explicit index selection for query execution.
     *
     * @readonly
     * @var string|null Index name to use or null for automatic index selection
     */
    protected string|null $indexHint = null;

    /**
     * Indicates whether to use a read-only database connection.
     * Supports read/write separation pattern in distributed systems.
     *
     * @readonly
     * @var bool|null True for read connection, false for write, null for default
     */
    protected bool|null $useReadConnection = false;

    /**
     * Specifies the NO LOCK hint for SQL Server compatibility.
     * Affects transaction isolation behavior.
     *
     * @readonly
     * @var string|null NO LOCK hint specification or null for default locking behavior
     */
    protected string|null $noLock = null;

    /**
     * Retrieves query results, supporting UnitOfWork if needed.
     *
     * @param bool $addToUnitOfWork If true, query will be deferred for execution.
     *
     * @return static|Arrhae The query results.
     * @throws QueryBuilderException
     * @throws JsonException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(bool $addToUnitOfWork = false) : static|Arrhae
    {
        if ($this->cacheEnabled && ($cached = $this->fetchFromCache())) {
            return new Arrhae(items: $cached);
        }

        $query = $this->buildSelectQuery();
        $pdo   = $this->getDatabaseConnection();
        $stmt  = $pdo->prepare(query: $query);

        if ($addToUnitOfWork) {
            $this
                ->getUnitOfWork()
                ->registerQuery(
                    operation : QueryBuilderEnum::QUERY_TYPE_SELECT,
                    statement : $stmt,
                    pdo       : $pdo,
                    parameters: $this->getParameters(),
                );

            return $this;
        }

        return $this->executeQuery(stmt: $stmt);
    }

    /**
     * Attempts to retrieve a cached result.
     *
     * @throws QueryBuilderException|\JsonException|\Psr\SimpleCache\InvalidArgumentException
     */
    private function fetchFromCache() : array|null
    {
        if (! $this->cache || ! $this->cacheEnabled) {
            return null;
        }

        $key = $this->generateCacheKey();

        return $this->cache->has(key: $key) ? $this->cache->get(key: $key) : null;
    }

    /**
     * Generates a unique cache key for the query.
     *
     * This function generates a unique cache key for an SQL `SELECT` query to reduce redundancy in repeated database
     * queries. It combines the generated query (from the `buildSelectQuery` method) and its parameters (from
     * `getParameters`) into a JSON format, using specific encoding options to ensure readability and
     * accuracy. The resulting JSON string is then hashed using the `xxh128` algorithm, which is fast and efficient,
     * and the generated hash is used as a unique cache identifier with the prefix `'query:'`. This method plays a
     * crucial role in implementing query caching, optimizing application performance, and reducing the number of
     * database calls.
     *
     * @throws QueryBuilderException|JsonException
     */
    private function generateCacheKey() : string
    {
        return 'query:' . hash(
                'xxh128',
                json_encode(
                    [
                        'query'      => $this->buildSelectQuery(),
                        'parameters' => $this->getParameters(),
                    ],
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                )
            );
    }

    /**
     * Generates the SELECT query string.
     *
     * @throws QueryBuilderException
     */
    private function buildSelectQuery() : string
    {
        return implode(
            ' ',
            array_filter(
                [
                    // Build the SELECT clause of the SQL query (e.g., "SELECT column1, column2").
                    $this->buildSelectClause(),

                    // Build the FROM clause of the SQL query (e.g., "FROM table_name").
                    $this->buildFromClause(),

                    // Build the JOIN clauses for the query, if any (e.g., "LEFT JOIN tableB ON ...").
                    $this->buildJoins(),

                    // Build the WHERE clause for the query, defining specific conditions (e.g., "WHERE column = value").
                    $this->buildWhereClauses(),

                    // Build the GROUP BY clause and HAVING condition for the query (e.g., "GROUP BY column HAVING COUNT(*) > 1").
                    $this->buildGroupByAndHaving(),

                    // Build the ORDER BY clause to sort the query results (e.g., "ORDER BY column ASC/DESC").
                    $this->buildOrderBy(),

                    // Build the LIMIT and OFFSET clauses to restrict the number of rows returned and set an offset (e.g., "LIMIT 10 OFFSET 20").
                    $this->buildLimitOffsetClause(),

                    // Lock mode (optional) used for concurrency (e.g., "FOR UPDATE" or "LOCK IN SHARE MODE").
                    $this->lockMode,

                    // Boolean property indicating whether a "NOLOCK" option should be added (used in specific database systems).
                    $this->noLock,
                ]
            )
        );
    }

    /**
     * Builds the SELECT clause.
     */
    private function buildSelectClause() : string
    {
        // Initialize the $columns array with 'DISTINCT' if the $this->distinct property is true,
        // otherwise, start with an empty array.
        $columns = $this->distinct ? ['DISTINCT'] : [];

        // Merge the current $columns array with $this->columns. If $this->columns is empty
        // or null, use ['*'] as the default (to select all columns).
        $columns = array_merge($columns, $this->columns ?: ['*']);

        // If the $this->windowFunction property is set (not null or falsy), append its
        // value to the $columns array. This is typically used for specialized SQL
        // window functions like ROW_NUMBER() or RANK().
        if ($this->windowFunction) {
            $columns[] = $this->windowFunction;
        }

        // Join all the elements of the $columns array into a comma-separated string
        // and prepend it with 'SELECT'. This constructs the final SQL SELECT clause.
        return 'SELECT ' . implode(', ', $columns);
    }

    /**
     * Builds the FROM clause.
     *
     * @throws QueryBuilderException
     */
    private function buildFromClause() : string
    {
        // Fetch the table name using `getTableName` and sanitize/quote it with `quoteIdentifier`.
        $table = $this->quoteIdentifier(name: $this->getTableName());

        // Check if the sanitized/quoted table name is empty.
        // Throws a `QueryBuilderException` if the table name is not provided.
        if (empty($table)) {
            throw new QueryBuilderException(message: 'Table name is required.');
        }

        // Return a string combining the SQL "FROM" clause and the quoted table name.
        return 'FROM ' . $table;
    }

    /**
     * Builds the LIMIT and OFFSET clauses.
     *
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     */
    private function buildLimitOffsetClause() : string|null
    {
        if ($this->limit !== null && $this->limit < 0) {
            // If the $limit property is set (not null) and its value is less than 0,
            // this indicates an invalid value since a limit must be non-negative.
            // A QueryBuilderException is thrown to prevent invalid SQL queries.
            throw new QueryBuilderException(message: 'Limit must be a non-negative integer.');
        }

        if ($this->offset !== null && $this->offset < 0) {
            // If the $offset property is set (not null) and its value is less than 0,
            // this indicates an invalid value since an offset must be non-negative.
            // A QueryBuilderException is thrown similarly to the limit check above.
            throw new QueryBuilderException(message: 'Offset must be a non-negative integer.');
        }

        return implode(
            ' ', // The delimiter used to concatenate the resulting parts of the SQL clause.
            array_filter(
                [
                    // If $this->limit is set (not null), a "LIMIT" clause is constructed as a string
                    // containing the value of the $this->limit property. Otherwise, null is returned.
                    $this->limit !== null ? "LIMIT {$this->limit}" : null,

                    // Similarly, if $this->offset is set (not null), an "OFFSET" clause is constructed
                    // as a string containing the value of the $this->offset property.
                    // Otherwise, null is returned.
                    $this->offset !== null ? "OFFSET {$this->offset}" : null,
                ]
            )
        // The `array_filter()` function is used to remove null values from the array.
        // This prevents unnecessary spaces or invalid SQL fragments if no limit or offset is set.
        );
    }

    /**
     * Executes the SELECT query and caches the result if enabled.
     *
     * @throws QueryBuilderException|JsonException|\Psr\SimpleCache\InvalidArgumentException
     */
    private function executeQuery(PDOStatement $stmt) : Arrhae
    {
        try {
            // Attempts to execute the prepared SQL statement with the query parameters
            // `getParameters()` presumably returns an array of parameters for the query.
            $stmt->execute(params: $this->getParameters());
        } catch (PDOException $exception) {
            dumpx('executeQuery() dump: ', $exception, $stmt);
            // If an exception occurs during query execution, a custom `QueryBuilderException` is thrown.
            // It includes details about the error, such as the exception message and the executed SQL query.
            throw new QueryBuilderException(message: 'Query execution failed.', previous: $exception);
        }

        // Wraps the result of the executed query in an `Arrhae` object.
        // `fetchAll` retrieves the data from the query as an associative array.
        $result = new Arrhae(items: $stmt->fetchAll(mode: PDO::FETCH_ASSOC));

        if ($this->cacheEnabled) { // Checks if caching is enabled before proceeding.
            // Stores the query results in the cache.
            // `generateCacheKey()` generates a unique key for the query for identification in the cache.
            // `toArray()` converts the `Arrhae` object back to a standard array for caching purposes.
            $this->cache->set(
                key  : $this->generateCacheKey(), // Unique key identifying the cached data.
                value: $result->toArray(),        // The query result data is cached as a plain array.
                ttl  : $this->cacheTTL            // Time-to-live for the cache item (defaults to 300 seconds).
            );
        }

        // Returns the wrapped query result (`Arrhae` object) to the caller.
        return $result;
    }
}
