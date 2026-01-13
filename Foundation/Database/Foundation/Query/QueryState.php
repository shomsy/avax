<?php

declare(strict_types=1);

namespace Avax\Database\Query;

use Avax\Database\Query\AST\JoinNode;
use Avax\Database\Query\AST\NestedWhereNode;
use Avax\Database\Query\AST\OrderNode;
use Avax\Database\Query\AST\WhereNode;
use Avax\Database\Query\ValueObjects\BindingBag;

/**
 * Immutable technical state container for database query metadata.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryStates.md
 */
final readonly class QueryState
{
    /**
     * @param string[]                         $columns       The list of technical column identifiers or expressions
     *                                                        for projection.
     * @param string|null                      $from          The primary technical identifier for the data source
     *                                                        (table).
     * @param JoinNode[]                       $joins         The collection of structural nodes representing source
     *                                                        relationships.
     * @param array<WhereNode|NestedWhereNode> $wheres        The hierarchical collection of logical filters and
     *                                                        branches.
     * @param string[]                         $groups        The collection of column identifiers used for result
     *                                                        aggregation.
     * @param array                            $havings       The collection of logical filters applied to aggregate
     *                                                        sets.
     * @param OrderNode[]                      $orders        The collection of structural nodes defining the result
     *                                                        set sequence.
     * @param int|null                         $limit         The strictly enforced upper limit of records to be
     *                                                        retrieved.
     * @param int|null                         $offset        The number of records to bypass before the retrieval
     *                                                        window starts.
     * @param array                            $values        The associative map of column/value pairs for mutation
     *                                                        operations.
     * @param string[]                         $updateColumns The specific technical columns targeted for update or
     *                                                        upsert logic.
     * @param bool                             $distinct      Toggle indicating if strictly unique records should be
     *                                                        projected.
     * @param BindingBag                       $bindings      The immutable container for secure, parameterized query
     *                                                        tokens.
     */
    public function __construct(
        public array       $columns = ['*'],
        public string|null $from = null,
        public array       $joins = [],
        public array       $wheres = [],
        public array       $groups = [],
        public array       $havings = [],
        public array       $orders = [],
        public int|null    $limit = null,
        public int|null    $offset = null,
        public array       $values = [],
        public array       $updateColumns = [],
        public bool        $distinct = false,
        private BindingBag $bindings = new BindingBag
    ) {}

    /**
     * Create a new state with the assigned target table (FROM).
     *
     * @param string $table Target table name.
     */
    public function withFrom(string $table) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'from' => $table]
        );
    }

    /**
     * Create a new state with the defined selection (SELECT) columns.
     *
     * @param string[] $columns Collection of column identifiers.
     */
    public function withColumns(array $columns) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'columns' => $columns]
        );
    }

    /**
     * Create a new state with the unique records flag (DISTINCT).
     *
     * @param bool $distinct Whether to project unique records.
     */
    public function withDistinct(bool $distinct = true) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'distinct' => $distinct]
        );
    }

    /**
     * Coordinate the setting of a strictly enforced record limit.
     *
     * -- intent:
     * Caps the number of records retrieved by the persistence engine,
     * typically used for pagination or existence checks.
     *
     * @param int|null $limit The maximum record volume allowed in the result set.
     *
     * @return self A fresh QueryState instance with the applied limit.
     */
    public function withLimit(int|null $limit) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'limit' => $limit]
        );
    }

    /**
     * Coordinate the setting of a record bypass offset.
     *
     * -- intent:
     * Skip a specified number of leading records in the retrieval window,
     * essential for deep-traversal pagination logic.
     *
     * @param int|null $offset The technical volume of records to skip.
     *
     * @return self A fresh QueryState instance with the applied offset.
     */
    public function withOffset(int|null $offset) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'offset' => $offset]
        );
    }

    /**
     * Coordinate the attachment of technical data for mutation operations.
     *
     * -- intent:
     * Stores the key-value map representing the new state to be persisted
     * in an INSERT or UPDATE context.
     *
     * @param array $values The associative map of technical column/value pairs.
     *
     * @return self A fresh QueryState instance with the applied mutation payload.
     */
    public function withValues(array $values) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'values' => $values]
        );
    }

    /**
     * Coordinate the targeting of specific columns for modification.
     *
     * -- intent:
     * Defines a subset of technical columns that should be updated,
     * typically used in complex UPSERT or partial UPDATE scenarios.
     *
     * @param string[] $columns The collection of technical identifiers allowed for update.
     *
     * @return self A fresh QueryState instance with the applied target columns.
     */
    public function withUpdateColumns(array $columns) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'updateColumns' => $columns]
        );
    }

    /**
     * Coordinate the addition of a source relationship (JOIN).
     *
     * -- intent:
     * Appends a new structural node defining a relationship with another
     * data source to the existing joins collection.
     *
     * @param JoinNode $join The structural node abstraction defining the join relationship.
     *
     * @return self A fresh QueryState instance with the added relationship.
     */
    public function addJoin(JoinNode $join) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'joins' => [...$this->joins, $join]]
        );
    }

    /**
     * Create a new state including a filter instruction (WHERE).
     *
     * @param WhereNode|NestedWhereNode $where Filter node data.
     */
    public function addWhere(WhereNode|NestedWhereNode $where) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'wheres' => [...$this->wheres, $where]]
        );
    }

    /**
     * Coordinate the addition of an aggregation column (GROUP BY).
     *
     * -- intent:
     * Incorporates a new technical identifier into the collection used
     * for record grouping and server-side analysis.
     *
     * @param string $column The structural identifier of the column to group by.
     *
     * @return self A fresh QueryState instance with the added grouping instruction.
     */
    public function addGroup(string $column) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'groups' => [...$this->groups, $column]]
        );
    }

    /**
     * Coordinate the bulk definition of record grouping criteria.
     *
     * -- intent:
     * Overwrites the current grouping collection with a new set of technical
     * identifiers for aggregation.
     *
     * @param string[] $groups The collection of column identifiers for aggregation.
     *
     * @return self A fresh QueryState instance with the bulk applied groups.
     */
    public function withGroups(array $groups) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'groups' => $groups]
        );
    }

    /**
     * Coordinate the addition of an aggregate result filter (HAVING).
     *
     * -- intent:
     * Appends a logical condition applied to groups/aggregates, maintaining
     * the collection of aggregate-level constraints.
     *
     * @param array $having The technical data representing an aggregate filter.
     *
     * @return self A fresh QueryState instance with the added aggregate filter.
     */
    public function addHaving(array $having) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'havings' => [...$this->havings, $having]]
        );
    }

    /**
     * Coordinate the addition of a result set ordering instruction (ORDER BY).
     *
     * -- intent:
     * Appends a sorting abstraction to the collection, defining the final
     * chronological or alphabetic sequence of the retrieved data.
     *
     * @param OrderNode $order The structural abstraction defining the sorting logic.
     *
     * @return self A fresh QueryState instance with the applied sorting.
     */
    public function addOrder(OrderNode $order) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'orders' => [...$this->orders, $order]]
        );
    }

    /**
     * Create a new state with a securely attached parameter token.
     *
     * @param mixed $value Raw data to be bound.
     */
    public function addBinding(mixed $value) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'bindings' => $this->bindings->with(value: $value)]
        );
    }

    /**
     * Coordinate the secure bulk attachment of technical parameter tokens.
     *
     * -- intent:
     * Facilitates the mass-parameterization of query values while
     * maintaining immutable state transitions.
     *
     * @param array $values The collection of raw data tokens to be secured.
     *
     * @return self A fresh QueryState instance with the merged parameter tokens.
     */
    public function mergeBindings(array $values) : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'bindings' => $this->bindings->merge(parameters: $values)]
        );
    }

    /**
     * Coordinate the complete reset of the secure parameter bag.
     *
     * -- intent:
     * Provides a clean slate for parameterization, typically used when a
     * query is reused or forked for a significantly different compilation.
     *
     * @return self A fresh QueryState instance with a cleared parameter bag.
     */
    public function resetBindings() : self
    {
        return new self(
            ...[...get_object_vars(object: $this), 'bindings' => new BindingBag]
        );
    }

    /**
     * Retrieve the linearized collection of secure parameter values.
     *
     * @return array<array-key, mixed>
     */
    public function getBindings() : array
    {
        return $this->bindings->all();
    }
}
