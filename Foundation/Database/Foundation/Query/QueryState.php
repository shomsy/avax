<?php

declare(strict_types=1);

namespace Avax\Database\Query;

/**
 * High-performance state container for builder metadata.
 *
 * -- intent: accumulate all query-building steps before final SQL compilation.
 */
final class QueryState
{
    /**
     * List of technical columns to retrieve.
     */
    public array $columns = ['*'];

    /**
     * Target database table name.
     */
    public string|null $from = null;

    /**
     * Relational join definitions.
     */
    public array $joins = [];

    /**
     * Logical filter constraints (WHERE).
     */
    public array $wheres = [];

    /**
     * Aggregation groups (GROUP BY).
     */
    public array $groups = [];

    /**
     * Aggregate filters (HAVING).
     */
    public array $havings = [];

    /**
     * Sorting rules (ORDER BY).
     */
    public array $orders = [];

    /**
     * Record retrieval limit.
     */
    public int|null $limit = null;

    /**
     * Record retrieval offset.
     */
    public int|null $offset = null;

    /**
     * Data mutation values (INSERT/UPDATE).
     */
    public array $values = [];

    /**
     * Mutation columns (UPSERT).
     */
    public array $updateColumns = [];

    /**
     * Whether to retrieve distinct records.
     */
    public bool $distinct = false;

    /**
     * Internal parameter bindings for PDO.
     */
    private array $bindings = [];

    /**
     * Register a new value for secure parameter binding.
     *
     * -- intent: ensure all user-provided data is handled via PDO placeholders.
     *
     * @param mixed $value Raw input data
     *
     * @return void
     */
    public function addBinding(mixed $value) : void
    {
        $this->bindings[] = $value;
    }

    /**
     * Retrieve the collection of registered bindings.
     *
     * -- intent: provide the executor with sanitized values.
     *
     * @return array
     */
    public function getBindings() : array
    {
        return $this->bindings;
    }

    /**
     * Purge the active binding registry.
     *
     * -- intent: reset parameters for multiple compilation passes.
     *
     * @return void
     */
    public function resetBindings() : void
    {
        $this->bindings = [];
    }
}


