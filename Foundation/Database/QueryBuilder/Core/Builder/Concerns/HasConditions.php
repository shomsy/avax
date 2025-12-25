<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\Query\Condition;
use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Closure;
use InvalidArgumentException;

/**
 * Trait HasConditions
 *
 * -- intent: extend the query builder with complex logical filtering capabilities (WHERE clauses).
 */
trait HasConditions
{
    /**
     * Add an "OR WHERE" clause to the query.
     *
     * -- intent: provide a shorthand for alternative logical constraints.
     *
     * @param string|Closure $column   Technical column name
     * @param mixed          $operator Comparison operator
     * @param mixed          $value    Comparison value
     *
     * @return QueryBuilder|HasConditions
     */
    public function orWhere(string|Closure $column, mixed $operator = null, mixed $value = null) : self
    {
        return $this->where(column: $column, operator: $operator, value: $value, boolean: 'OR');
    }

    /**
     * Add a basic where clause to the query.
     *
     * -- intent: provide a human-readable DSL for SQL WHERE constraints.
     *
     * @param string|Closure $column   Technical column name or sub-query closure
     * @param mixed          $operator Comparison operator or value if operator is omitted
     * @param mixed          $value    Target comparison value
     * @param string         $boolean  Logical joiner (AND/OR)
     *
     * @return QueryBuilder|HasConditions
     */
    public function where(
        string|Closure $column,
        mixed          $operator = null,
        mixed          $value = null,
        string         $boolean = 'AND'
    ) : self
    {
        if ($column instanceof Closure) {
            return $this->whereNested(callback: $column, boolean: $boolean);
        }

        if (func_num_args() === 2) {
            $value    = $operator;
            $operator = '=';
        }

        $this->state->wheres[] = new Condition(
            column  : $column,
            operator: (string) $operator,
            value   : $value,
            boolean : $boolean
        );

        if (! in_array(needle: $operator, haystack: ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'])) {
            $this->state->addBinding(value: $value);
        }

        return $this;
    }

    /**
     * Add a composite nested WHERE block to the query.
     *
     * -- intent: provide isolation for complex logical groups (using parentheses).
     *
     * @param Closure $callback Closure receiving a fresh builder for nesting
     * @param string  $boolean  Logical joiner
     *
     * @return QueryBuilder|HasConditions
     */
    protected function whereNested(Closure $callback, string $boolean = 'AND') : self
    {
        $query = new class(
            grammar           : $this->grammar,
            executor          : $this->executor,
            transactionManager: $this->transactionManager,
            identityMap       : $this->identityMap
        ) extends QueryBuilder {};

        $callback($query);

        $this->state->wheres[] = [
            'type'    => 'Nested',
            'query'   => $query,
            'boolean' => $boolean
        ];

        foreach ($query->state->getBindings() as $binding) {
            $this->state->addBinding(value: $binding);
        }

        return $this;
    }

    /**
     * Add an "OR WHERE IN" clause to the query.
     *
     * -- intent: provide shorthand for alternative set participation filters.
     *
     * @param string $column Technical column name
     * @param array  $values Collection of values
     *
     * @return QueryBuilder|HasConditions
     */
    public function orWhereIn(string $column, array $values) : self
    {
        return $this->whereIn(column: $column, values: $values, boolean: 'OR');
    }

    /**
     * Add a "WHERE IN" clause to the query.
     *
     * -- intent: filter records based on a predefined set of values.
     *
     * @param string      $column  Technical column name
     * @param array       $values  Collection of values
     * @param string|null $boolean Logical joiner
     * @param bool        $not     Whether to use NOT IN
     *
     * @return QueryBuilder|HasConditions
     */
    public function whereIn(string $column, array $values, string|null $boolean = null, bool $not = false) : self
    {
        $boolean  ??= 'AND';
        $operator = $not ? 'NOT IN' : 'IN';

        $this->state->wheres[] = new Condition(
            column  : $column,
            operator: $operator,
            value   : $values,
            boolean : $boolean
        );

        foreach ($values as $value) {
            $this->state->addBinding(value: $value);
        }

        return $this;
    }

    /**
     * Add a "WHERE BETWEEN" clause to the query.
     *
     * -- intent: filter records within a numeric or temporal range.
     *
     * @param string      $column  Technical column name
     * @param array       $values  Start and end values
     * @param string|null $boolean Logical joiner
     * @param bool        $not     Whether to use NOT BETWEEN
     *
     * @return QueryBuilder|HasConditions
     */
    public function whereBetween(string $column, array $values, string|null $boolean = null, bool $not = false) : self
    {
        $boolean ??= 'AND';
        if (count(value: $values) !== 2) {
            throw new InvalidArgumentException(message: "BETWEEN requires exactly two values.");
        }

        $operator = $not ? 'NOT BETWEEN' : 'BETWEEN';

        $this->state->wheres[] = new Condition(
            column  : $column,
            operator: $operator,
            value   : $values,
            boolean : $boolean
        );

        $this->state->addBinding(value: $values[0]);
        $this->state->addBinding(value: $values[1]);

        return $this;
    }
}


