<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder;

use Avax\Database\Identity\IdentityMap;
use Avax\Database\Query\QueryState;
use Avax\Database\QueryBuilder\Core\Executor\QueryOrchestrator;
use Avax\Database\QueryBuilder\Core\Grammar\GrammarInterface;
use Avax\Database\QueryBuilder\Exceptions\InvalidCriteriaException;
use Avax\Database\QueryBuilder\ValueObjects\Expression;
use ReflectionClass;
use Throwable;

/**
 * Fluent, immutable builder for compiling and executing SQL queries.
 *
 * Supports dialect-aware compilation, safe bindings, pretend mode, and optional deferred execution via IdentityMap.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryBuilder.md
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

    /** @var QueryState The internal "memory" of all the blocks (table, filters, columns) we've added so far. */
    protected QueryState $state;

    /** @var bool If true, we don't save changes immediately. We wait and do them all at once later. */
    protected bool $isDeferred = false;

    /**
     * Set up the builder with its two "helpers".
     *
     * @param GrammarInterface  $grammar      The "Translator". It knows how to turn your PHP code into specific SQL
     *                                        for MySQL/SQLite/etc.
     * @param QueryOrchestrator $orchestrator The "Conductor". It doesn't write SQL, but it knows how to send the final
     *                                        SQL to the database and get results back.
     *
     * @throws \ReflectionException
     */
    public function __construct(
        protected readonly GrammarInterface $grammar,
        protected QueryOrchestrator         $orchestrator
    )
    {
        $this->state = new QueryState();

        // If this class has a 'tableName' property defined (like in a Model), we use it as the default target.
        if (property_exists(object_or_class: $this, property: 'tableName')) {
            $ref       = new ReflectionClass(objectOrClass: $this);
            $tableName = $ref->getProperty(name: 'tableName')->getValue(object: $this);

            if (is_string(value: $tableName) && $tableName !== '') {
                $this->state = $this->state->withFrom(table: $tableName);
            }
        }
    }

    /**
     * Create a perfect copy of this builder.
     *
     * -- intent:
     * This is the "Save As" mechanism. It ensures that when you branch off a
     * common search, you don't mess up the original search object.
     */
    public function __clone()
    {
        $this->orchestrator = clone $this->orchestrator;
    }

    /**
     * Start a brand new, empty query using the same database setup.
     *
     * @return static A fresh builder instance with no filters or tables set.
     * @throws \ReflectionException
     */
    public function newQuery() : static
    {
        return new static(
            grammar     : $this->grammar,
            orchestrator: $this->orchestrator
        );
    }

    /**
     * Inject a raw SQL fragment without quoting or escaping.
     *
     * Use only for trusted literals (e.g., functions/CASE expressions), never for user input.
     *
     * @param string $value SQL fragment to include verbatim.
     *
     * @return Expression
     * @throws InvalidCriteriaException When the fragment contains disallowed characters.
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryBuilder.md#raw
     */
    public function raw(string $value) : Expression
    {
        $this->assertSafeRawExpression(expression: $value, context: 'raw');

        return new Expression(value: $value);
    }

    /**
     * Security Check: Make sure raw SQL fragments aren't dangerous.
     *
     * @param string $expression The text to check.
     * @param string $context    Where this check is happening (for error messages).
     *
     * @throws InvalidCriteriaException If dangerous characters are found.
     */
    private function assertSafeRawExpression(string $expression, string $context) : void
    {
        if ($expression === '') {
            throw new InvalidCriteriaException(method: $context, reason: "Raw expressions must not be empty.");
        }

        if (preg_match(pattern: '/[;]|--|\\/\\*/', subject: $expression) === 1) {
            throw new InvalidCriteriaException(
                method: $context,
                reason: "Raw expressions must not contain statement terminators or comments."
            );
        }

        if (preg_match(pattern: '/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/', subject: $expression) === 1) {
            throw new InvalidCriteriaException(
                method: $context,
                reason: "Raw expressions must not contain control characters."
            );
        }

        if (preg_match(pattern: '/^[\\x20-\\x7E]+$/', subject: $expression) !== 1) {
            throw new InvalidCriteriaException(
                method: $context,
                reason: "Raw expressions must be plain ASCII characters to allow safe inspection."
            );
        }
    }

    /**
     * Enable dry-run mode that logs SQL without executing it.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryBuilder.md#pretend
     * @return self Builder clone in pretend mode.
     */
    public function pretend() : self
    {
        $clone = clone $this;
        $clone->orchestrator->pretend(value: true);

        return $clone;
    }

    /**
     * Execute a raw SQL "Statement" that doesn't return data (e.g., cleanup commands).
     *
     * @param string $query    SQL command to run.
     * @param array  $bindings Parameter bindings for the command.
     *
     * @return bool True if the database accepted the command.
     * @throws Throwable
     */
    public function statement(string $query, array $bindings = []) : bool
    {
        return $this->orchestrator->execute(sql: $query, bindings: $bindings)->isSuccessful();
    }

    /**
     * Set the target table.
     *
     * @param string $table Table name.
     *
     * @return self
     */
    public function from(string $table) : self
    {
        $clone        = clone $this;
        $clone->state = $this->state->withFrom(table: $table);

        return $clone;
    }

    /**
     * Mix raw SQL into your column selection.
     *
     * @param string ...$expressions Raw SQL snippets for selection.
     *
     * @return self
     * @throws InvalidCriteriaException When the fragment contains disallowed characters.
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryBuilder.md#selectraw
     */
    public function selectRaw(string ...$expressions) : self
    {
        foreach ($expressions as $expression) {
            $this->assertSafeRawExpression(expression: $expression, context: 'selectRaw');
        }

        $clone        = clone $this;
        $clone->state = $this->state->withColumns(columns: array_merge($this->state->columns ?: [], $expressions));

        return $clone;
    }

    /**
     * Remove all duplicate rows from your results.
     *
     * @return self A builder copy with the UNIQUE/DISTINCT filter active.
     */
    public function distinct() : self
    {
        $clone        = clone $this;
        $clone->state = $this->state->withDistinct(distinct: true);

        return $clone;
    }

    /**
     * Skip the first X number of rows.
     *
     * -- intent:
     * Essential for "Page 2" or "Page 3" of results. If each page has 10
     * items, Page 2 would skip the first 10.
     *
     * @param int $offset How many rows to jump over.
     *
     * @return self A builder copy with this skip applied.
     */
    public function offset(int $offset) : self
    {
        if ($offset < 0) {
            throw new InvalidCriteriaException(method: 'offset', reason: "OFFSET must be a non-negative integer.");
        }

        $clone        = clone $this;
        $clone->state = $this->state->withOffset(offset: $offset);

        return $clone;
    }

    /**
     * Defer mutations into an IdentityMap instead of executing immediately.
     *
     * Requires an available IdentityMap (provided or already on the orchestrator).
     *
     * @param IdentityMap|null $identityMap Optional map to use for this query.
     *
     * @return self New builder instance with deferred execution enabled.
     * @throws InvalidCriteriaException When no IdentityMap is available.
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryBuilder.md#deferred
     */
    public function deferred(IdentityMap|null $identityMap = null) : self
    {
        $clone             = clone $this;
        $clone->isDeferred = true;

        if ($identityMap !== null) {
            $clone->orchestrator = $clone->orchestrator->withIdentityMap(map: $identityMap);
        }

        if ($clone->orchestrator->getIdentityMap() === null) {
            throw new InvalidCriteriaException(
                method: 'deferred',
                reason: "IdentityMap must be available via Orchestrator or provided to use deferred execution."
            );
        }

        return $clone;
    }

    /**
     * Quickly check if ANY records exist matching your search.
     *
     * @return bool
     * @throws Throwable
     */
    public function exists() : bool
    {
        $instance = clone $this;

        if (method_exists(object_or_class: $instance, method: 'withSoftDeleteFilter')) {
            $instance = $instance->withSoftDeleteFilter();
        }

        $sql    = $instance->grammar->compileSelect(state: $instance->limit(limit: 1)->state);
        $result = $instance->orchestrator->query(sql: $sql, bindings: $instance->state->getBindings());

        return ! empty($result);
    }

    /**
     * Apply a maximum ceiling to the number of rows returned.
     *
     * @param int $limit Maximum results to retrieve.
     *
     * @return self
     * @throws InvalidCriteriaException If limit is negative.
     */
    public function limit(int $limit) : self
    {
        if ($limit < 0) {
            throw new InvalidCriteriaException(method: 'limit', reason: "LIMIT must be a non-negative integer.");
        }

        $clone        = clone $this;
        $clone->state = $this->state->withLimit(limit: $limit);

        return $clone;
    }

    /**
     * Persist a new record into the database.
     *
     * @param array $values Associative array of column => values.
     *
     * @return bool
     * @throws Throwable
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryBuilder.md#insert
     */
    public function insert(array $values) : bool
    {
        $clone        = clone $this;
        $clone->state = $this->state->withValues(values: $values);
        $sql          = $clone->grammar->compileInsert(state: $clone->state);

        return $clone->orchestrator->execute(
            sql      : $sql,
            bindings : $clone->state->getBindings(),
            operation: $this->isDeferred ? 'INSERT' : null
        )->isSuccessful();
    }

    /**
     * Modify existing records in the database.
     *
     * @param array $values Associative array of updates.
     *
     * @return bool
     * @throws Throwable
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryBuilder.md#update
     */
    public function update(array $values) : bool
    {
        $instance = clone $this;

        $instance        = $instance->withSoftDeleteFilter();
        $instance->state = $instance->state->withValues(values: $values);
        $sql             = $instance->grammar->compileUpdate(state: $instance->state);

        return $instance->orchestrator->execute(
            sql      : $sql,
            bindings : $instance->state->getBindings(),
            operation: $this->isDeferred ? 'UPDATE' : null
        )->isSuccessful();
    }

    /**
     * Remove matching records from the database.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryBuilder.md#delete
     * @return bool
     * @throws Throwable
     */
    public function delete() : bool
    {
        $instance = clone $this;

        if (method_exists(object_or_class: $instance, method: 'withSoftDeleteFilter')) {
            $instance = $instance->withSoftDeleteFilter();
        }

        $sql = $instance->grammar->compileDelete(state: $instance->state);

        return $instance->orchestrator->execute(
            sql      : $sql,
            bindings : $instance->state->getBindings(),
            operation: $this->isDeferred ? 'DELETE' : null
        )->isSuccessful();
    }

    /**
     * Retrieve a flattened array of values from a single column.
     *
     * @param string      $value Target column name.
     * @param string|null $key   Optional column to use for array keys.
     *
     * @return array
     * @throws \Throwable
     */
    public function pluck(string $value, string|null $key = null) : array
    {
        $columns = $key ? [$value, $key] : [$value];
        $results = $this->select(...$columns)->get();

        $pluck = [];
        foreach ($results as $result) {
            if ($key) {
                $pluck[$result[$key]] = $result[$value];
                continue;
            }
            $pluck[] = $result[$value];
        }

        return $pluck;
    }

    /**
     * Execute the retrieval query and return all matching records.
     *
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryBuilder.md#get
     * @return array<array-key, mixed>
     * @throws Throwable
     */
    public function get() : array
    {
        $instance = clone $this;

        if (method_exists(object_or_class: $instance, method: 'withSoftDeleteFilter')) {
            $instance = $instance->withSoftDeleteFilter();
        }

        $sql = $instance->grammar->compileSelect(state: $instance->state);

        return $instance->orchestrator->query(sql: $sql, bindings: $instance->state->getBindings());
    }

    /**
     * Select specific columns.
     *
     * @param string ...$columns Columns to include (defaults to `*` when empty).
     *
     * @return self
     */
    public function select(string ...$columns) : self
    {
        $clone        = clone $this;
        $clone->state = $this->state->withColumns(columns: empty($columns) ? ['*'] : $columns);

        return $clone;
    }

    /**
     * Retrieve a single scalar value from the first matching record.
     *
     * @param string $column  Target column name.
     * @param mixed  $default Fallback value if no record exists.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function value(string $column, mixed $default = null) : mixed
    {
        $result = $this->first();

        return $result[$column] ?? $default;
    }

    /**
     * Execute the query and return the first matching record.
     *
     * @param string|callable|null $key     Optional column or transform callback.
     * @param mixed                $default Fallback value if no record exists.
     *
     * @return mixed
     * @throws \Throwable
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
     * Retrieve the total number of records matching the query.
     *
     * @param string $column Column to count (defaults to '*').
     *
     * @return int
     * @throws \Throwable
     */
    public function count(string $column = '*') : int
    {
        $instance        = clone $this;
        $instance->state = $instance->state->withColumns(columns: ["COUNT({$column}) as aggregate"]);
        $result          = $instance->first();

        return (int) ($result['aggregate'] ?? 0);
    }

    /**
     * Retrieve a single record by its primary identity.
     *
     * @param mixed  $id     Identity value.
     * @param string $column Field name for the identity (defaults to 'id').
     *
     * @return mixed
     * @throws \Throwable
     */
    public function find(mixed $id, string $column = 'id') : mixed
    {
        return $this->where(column: $column, operator: '=', value: $id)->first();
    }

    /**
     * Get the internal state of the builder (the AST).
     */
    public function getState() : QueryState
    {
        return $this->state;
    }

    /**
     * Execute a closure within a database transaction.
     *
     * @param callable $callback Logic to run transactionally.
     *
     * @return mixed
     * @throws Throwable
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryBuilder.md#transaction
     */
    public function transaction(callable $callback) : mixed
    {
        return $this->orchestrator->transaction(callback: fn() => $callback($this));
    }
}
