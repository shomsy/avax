<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder;

use Avax\Database\Identity\IdentityMap;
use Avax\Database\Query\QueryState;
use Avax\Database\QueryBuilder\Core\Executor\ExecutorInterface;
use Avax\Database\QueryBuilder\Core\Grammar\GrammarInterface;
use Avax\Database\QueryBuilder\Exceptions\InvalidCriteriaException;
use Avax\Database\QueryBuilder\ValueObjects\Expression;
use Avax\Database\Transaction\Contracts\TransactionManagerInterface;
use Avax\Database\Transaction\Exceptions\TransactionException;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * The primary entry point for constructing and executing domain-fluent SQL queries.
 *
 * -- intent: provide a high-level DSL balanced with robust infrastructure coordination.
 */
class QueryBuilder
{
    use Concerns\Macroable;
    use Concerns\HasConditions;
    use Concerns\HasJoins;
    use Concerns\HasOrders;
    use Concerns\HasAggregates;
    use Concerns\HasGroups;
    use Concerns\HasControlStructures;
    use Concerns\HasSoftDeletes;
    use Concerns\HasAdvancedMutations;
    use Concerns\HasSchema;

    /**
     * Current state of the builder's parameters.
     */
    protected readonly QueryState $state;

    /**
     * Flag indicating if execution should be deferred.
     */
    protected bool $isDeferred = false;

    /**
     * Flag indicating if execution should be simulated.
     */
    protected bool $isPretending = false;

    /**
     * Constructor using PHP 8.3 property promotion for core engines.
     *
     * -- intent: initialize the builder with its core engine dependencies and query state.
     *
     * @param GrammarInterface                 $grammar            The compiler for dialect-specific SQL
     * @param ExecutorInterface                $executor           The technician for query execution
     * @param TransactionManagerInterface|null $transactionManager The coordinator for atomicity
     * @param IdentityMap|null                 $identityMap        The vault for deferred operations
     *
     * @throws ReflectionException If class introspection fails
     */
    public function __construct(
        protected readonly GrammarInterface                 $grammar,
        protected readonly ExecutorInterface                $executor,
        protected readonly TransactionManagerInterface|null $transactionManager = null,
        protected IdentityMap|null                          $identityMap = null
    )
    {
        $this->state = new QueryState();

        if (property_exists(object_or_class: $this, property: 'tableName')) {
            $ref       = new ReflectionClass(objectOrClass: $this);
            $tableName = $ref->getProperty(name: 'tableName')->getValue(object: $this);

            if (is_string(value: $tableName) && $tableName !== '') {
                $this->state->from = $tableName;
            }
        }
    }

    /**
     * Deep clone the builder to ensure decoupled state branches.
     *
     * -- intent: prevent state leakage between derived query instances.
     *
     * @return void
     */
    public function __clone()
    {
        $this->state = clone $this->state;
    }

    /**
     * Spawn a clean builder instance sharing the same foundational engines.
     *
     * -- intent: create a fresh query context without re-injecting core dependencies manually.
     *
     * @return static
     * @throws ReflectionException
     */
    public function newQuery() : static
    {
        return new static(
            grammar           : $this->grammar,
            executor          : $this->executor,
            transactionManager: $this->transactionManager,
            identityMap       : $this->identityMap
        );
    }

    /**
     * Create a raw SQL expression that will not be escaped or parameterized.
     *
     * -- intent: allow injection of literal SQL fragments for advanced operations.
     *
     * @param string $value The raw SQL fragment
     *
     * @return Expression
     */
    public function raw(string $value) : Expression
    {
        return new Expression(value: $value);
    }


    /**
     * Enable simulation (pretend) mode.
     */
    public function pretend() : self
    {
        $this->isPretending = true;

        return $this;
    }

    /**
     * Execute a raw SQL statement or simulate it.
     */
    public function statement(string $query, array $bindings = []) : bool
    {
        if ($this->isPretending) {
            echo "\033[33m[DRY RUN]\033[0m SQL: {$query}\n";

            return true;
        }

        return $this->executor->execute(sql: $query, bindings: $bindings)->isSuccessful();
    }

    /**
     * Target a specific database table for the query operations.
     *
     * -- intent: set the primary data source for the query.
     *
     * @param string $table The domain table name
     *
     * @return self
     */
    public function from(string $table) : self
    {
        $this->state->from = $table;

        return $this;
    }

    /**
     * Define the specific columns to be retrieved.
     *
     * -- intent: restrict the result set to the requested technical identifiers.
     *
     * @param string ...$columns List of column names
     *
     * @return self
     */
    public function select(string ...$columns) : self
    {
        $this->state->columns = empty($columns) ? ['*'] : $columns;

        return $this;
    }

    /**
     * Inject raw SQL fragments into the select clause.
     *
     * -- intent: allow bypass of the grammar's column wrapping for complex expressions.
     *
     * @param string ...$expressions Raw SQL expressions
     *
     * @return self
     * @throws InvalidCriteriaException On detected SQL injection attempts
     */
    public function selectRaw(string ...$expressions) : self
    {
        foreach ($expressions as $expression) {
            if (str_contains(haystack: $expression, needle: ';') || str_contains(haystack: $expression, needle: '--')) {
                throw new InvalidCriteriaException(
                    method: 'selectRaw',
                    reason: "Raw SELECT expressions must not contain semicolons or SQL comments."
                );
            }
        }

        $this->state->columns = array_merge($this->state->columns ?: [], $expressions);

        return $this;
    }

    /**
     * Enforce unique result sets via the DISTINCT operator.
     *
     * -- intent: filter out duplicate records from the final dataset.
     *
     * @return self
     */
    public function distinct() : self
    {
        $this->state->distinct = true;

        return $this;
    }

    /**
     * Skip a specific number of records at the beginning of the result set.
     *
     * -- intent: coordinate with limit for precise data windowing.
     *
     * @param int $offset Records to bypass
     *
     * @return self
     * @throws InvalidCriteriaException On negative integer inputs
     */
    public function offset(int $offset) : self
    {
        if ($offset < 0) {
            throw new InvalidCriteriaException(method: 'offset', reason: "OFFSET must be a non-negative integer.");
        }

        $this->state->offset = $offset;

        return $this;
    }

    /**
     * Enable deferred execution mode (Identity Map / Unit of Work).
     *
     * -- intent: buffer mutation operations for optimized batch processing.
     *
     * @param IdentityMap|null $identityMap Optional specific map to use
     *
     * @return self
     * @throws InvalidCriteriaException If no identity map is available for tracking
     */
    public function deferred(IdentityMap|null $identityMap = null) : self
    {
        $this->isDeferred = true;

        if ($identityMap !== null) {
            $this->identityMap = $identityMap;
        }

        if (! $this->identityMap) {
            throw new InvalidCriteriaException(
                method: 'deferred',
                reason: "IdentityMap must be provided to use deferred execution."
            );
        }

        return $this;
    }

    /**
     * Determine if any records matching the current criteria exist.
     *
     * -- intent: execute a minimal existence probe for binary result checking.
     *
     * @return bool
     * @throws Throwable If SQL execution fails
     */
    public function exists() : bool
    {
        $instance = clone $this;
        $sql      = $instance->grammar->compileSelect(state: $instance->limit(limit: 1)->state);
        $result   = $instance->executor->query(sql: $sql, bindings: $instance->state->getBindings());

        return ! empty($result);
    }

    /**
     * Restrict the number of records returned by the query.
     *
     * -- intent: optimize performance and facilitate pagination boundaries.
     *
     * @param int $limit Maximum record count
     *
     * @return self
     * @throws InvalidCriteriaException On negative integer inputs
     */
    public function limit(int $limit) : self
    {
        if ($limit < 0) {
            throw new InvalidCriteriaException(method: 'limit', reason: "LIMIT must be a non-negative integer.");
        }

        $this->state->limit = $limit;

        return $this;
    }

    /**
     * Retrieve the first record from the query result set.
     *
     * -- intent: isolate a single record from the results with optional path resolution.
     *
     * @param string|callable|null $key     Target column or processing closure
     * @param mixed                $default Fallback if no result is found
     *
     * @return mixed
     * @throws Throwable If SQL execution fails
     */
    public function first(string|callable|null $key = null, mixed $default = null) : mixed
    {
        $instance = clone $this;
        $result   = $instance->limit(limit: 1)->get();

        if (empty($result)) {
            return $default;
        }

        $firstRecord = $result[0];

        if ($key === null) {
            return $firstRecord;
        }

        if (is_callable(value: $key)) {
            return $key($firstRecord) ?? $default;
        }

        if (str_contains(haystack: $key, needle: '.')) {
            $keys  = explode(separator: '.', string: $key);
            $value = $firstRecord;

            foreach ($keys as $segment) {
                if (is_array(value: $value) && array_key_exists(key: $segment, array: $value)) {
                    $value = $value[$segment];
                } else {
                    return $default;
                }
            }

            return $value;
        }

        return $firstRecord[$key] ?? $default;
    }

    /**
     * Fulfill a SELECT query and retrieve all resulting records.
     *
     * -- intent: compile the final SQL and execute the physical data retrieval.
     *
     * @return array<array-key, mixed> Resulting dataset
     * @throws Throwable If SQL syntax or execution fails
     */
    public function get() : array
    {
        if (method_exists(object_or_class: $this, method: 'applySoftDeleteFilter')) {
            $this->applySoftDeleteFilter();
        }

        $sql = $this->grammar->compileSelect(state: $this->state);

        return $this->executor->query(sql: $sql, bindings: $this->state->getBindings());
    }

    /**
     * Execute an INSERT mutation query.
     *
     * -- intent: transform values into a physical data insertion instruction.
     *
     * @param array $values Columns and their corresponding values
     *
     * @return bool
     * @throws Throwable If insertion fails or deferral technician is missing
     */
    public function insert(array $values) : bool
    {
        $this->state->values = $values;
        $sql                 = $this->grammar->compileInsert(state: $this->state);

        if ($this->isDeferred) {
            $this->identityMap->schedule(operation: 'INSERT', sql: $sql, bindings: $this->state->getBindings());

            return true;
        }

        return $this->executor->execute(sql: $sql, bindings: $this->state->getBindings())->isSuccessful();
    }

    /**
     * Execute an UPDATE mutation query.
     *
     * -- intent: transform values and criteria into a physical data modification instruction.
     *
     * @param array $values Target assignments
     *
     * @return bool
     * @throws Throwable If update fails
     */
    public function update(array $values) : bool
    {
        if (method_exists(object_or_class: $this, method: 'applySoftDeleteFilter')) {
            $this->applySoftDeleteFilter();
        }

        $this->state->values = $values;
        $sql                 = $this->grammar->compileUpdate(state: $this->state);

        if ($this->isDeferred) {
            $this->identityMap->schedule(operation: 'UPDATE', sql: $sql, bindings: $this->state->getBindings());

            return true;
        }

        return $this->executor->execute(sql: $sql, bindings: $this->state->getBindings())->isSuccessful();
    }

    /**
     * Execute a DELETE query against the target table.
     *
     * -- intent: physically or logically remove records based on active criteria.
     *
     * @return bool
     * @throws Throwable If deletion fails
     */
    public function delete() : bool
    {
        if (method_exists(object_or_class: $this, method: 'applySoftDeleteFilter')) {
            $this->applySoftDeleteFilter();
        }

        $sql = $this->grammar->compileDelete(state: $this->state);

        if ($this->isDeferred) {
            $this->identityMap->schedule(operation: 'DELETE', sql: $sql, bindings: $this->state->getBindings());

            return true;
        }

        return $this->executor->execute(sql: $sql, bindings: $this->state->getBindings())->isSuccessful();
    }

    /**
     * Access the raw internal state for advanced structural metadata retrieval.
     *
     * -- intent: provide programmatic access to the accumulated query parameters.
     *
     * @return QueryState
     */
    public function getState() : QueryState
    {
        return $this->state;
    }

    /**
     * Execute a closure within a managed database transaction.
     *
     * -- intent: ensure atomic execution of multiple builder operations with automatic safety.
     *
     * @param callable $callback Operational closure
     *
     * @return mixed
     * @throws TransactionException If no transaction manager is present or execution fails
     * @throws Throwable If the transaction or callback fails
     */
    public function transaction(callable $callback) : mixed
    {
        if (! $this->transactionManager) {
            throw new TransactionException(message: "Transaction manager not set in builder.", nestingLevel: 0);
        }

        try {
            return $this->transactionManager->transaction(callback: fn () => $callback($this));
        } catch (Throwable $e) {
            if ($e instanceof TransactionException) {
                throw $e;
            }
            throw new TransactionException(message: $e->getMessage(), nestingLevel: 1, previous: $e);
        }
    }
}
