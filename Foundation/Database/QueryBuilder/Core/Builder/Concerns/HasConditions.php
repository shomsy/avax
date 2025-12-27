<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\Query\AST\NestedWhereNode;
use Avax\Database\Query\AST\WhereNode;
use Closure;
use InvalidArgumentException;

/**
 * Trait providing high-level logical filtering (WHERE) capabilities for the QueryBuilder.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Filtering.md
 */
trait HasConditions
{
    /**
     * Add a basic filtering criterion to the current query context.
     *
     * @param string|Closure $column   Field name or nested logic closure.
     * @param mixed          $operator Comparison operator or value.
     * @param mixed          $value    Comparison value.
     * @param string         $boolean  Logical joiner ('AND' or 'OR').
     * @return self
     */
    public function where(
        string|Closure $column,
        mixed          $operator = null,
        mixed          $value = null,
        string         $boolean = 'AND'
    ): self {
        if ($column instanceof Closure) {
            return $this->whereNested(callback: $column, boolean: $boolean);
        }

        if (func_num_args() === 2) {
            $value    = $operator;
            $operator = '=';
        }

        $clone        = clone $this;
        $clone->state = $clone->state->addWhere(where: new WhereNode(
            column: $column,
            operator: (string) $operator,
            value: $value,
            boolean: $boolean
        ));

        if (! in_array(needle: $operator, haystack: ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'])) {
            $clone->state = $clone->state->addBinding(value: $value);
        }

        return $clone;
    }

    /**
     * Add an "OR WHERE" criterion to the current query context.
     *
     * @param string|Closure $column   Field name or nested logic closure.
     * @param mixed          $operator Comparison operator.
     * @param mixed          $value    Comparison value.
     * @return self
     */
    public function orWhere(string|Closure $column, mixed $operator = null, mixed $value = null): self
    {
        return $this->where(column: $column, operator: $operator, value: $value, boolean: 'OR');
    }

    /**
     * Filter results by a membership check against a specific collection of values.
     *
     * -- intent:
     * Provide an expressive DSL for SQL "IN" and "NOT IN" logic, 
     * handling bulk parameter binding automatically.
     *
     * @param string      $column  The technical field name to check for membership.
     * @param array       $values  The collection of allowed data tokens.
     * @param string|null $boolean The logical joiner ('AND' or 'OR').
     * @param bool        $not     Flag indicating whether to use negative (NOT IN) logic.
     * @return self A fresh, cloned builder instance with the membership filter.
     */
    public function whereIn(string $column, array $values, string|null $boolean = null, bool $not = false): self
    {
        $boolean  ??= 'AND';
        $operator = $not ? 'NOT IN' : 'IN';

        $clone        = clone $this;
        $clone->state = $clone->state->addWhere(where: new WhereNode(
            column: $column,
            operator: $operator,
            value: $values,
            boolean: $boolean
        ));

        $clone->state = $clone->state->mergeBindings(values: $values);

        return $clone;
    }

    /**
     * Add an "OR WHERE IN" criterion to the current query context.
     *
     * -- intent:
     * Provide an alternative membership filtering branch, acting as a 
     * shorthand for whereIn() with the 'OR' boolean joiner.
     *
     * @param string $column The technical field name to check.
     * @param array  $values The collection of allowed data tokens.
     * @return self A fresh, cloned builder instance with the OR membership filter.
     */
    public function orWhereIn(string $column, array $values): self
    {
        return $this->whereIn(column: $column, values: $values, boolean: 'OR');
    }

    /**
     * Filter results by verifying a column's value falls within a specified range.
     *
     * -- intent:
     * Provide an expressive DSL for SQL "BETWEEN" and "NOT BETWEEN" logic,
     * ensuring exactly two values are provided for the range.
     *
     * @param string      $column  The technical field name to check.
     * @param array       $values  A pair of values defining the inclusive range.
     * @param string|null $boolean The logical joiner ('AND' or 'OR').
     * @param bool        $not     Flag indicating whether to use negative (NOT BETWEEN) logic.
     * @throws InvalidArgumentException If the provided values array does not contain exactly two items.
     * @return self A fresh, cloned builder instance with the range filter.
     */
    public function whereBetween(string $column, array $values, string|null $boolean = null, bool $not = false): self
    {
        $boolean ??= 'AND';
        if (count(value: $values) !== 2) {
            throw new InvalidArgumentException(message: "BETWEEN requires exactly two values.");
        }

        $operator = $not ? 'NOT BETWEEN' : 'BETWEEN';

        $clone        = clone $this;
        $clone->state = $clone->state->addWhere(where: new WhereNode(
            column: $column,
            operator: $operator,
            value: $values,
            boolean: $boolean
        ));

        $clone->state = $clone->state->mergeBindings(values: $values);

        return $clone;
    }

    /**
     * Coordinate the addition of a nested logical branch (grouped conditions).
     *
     * -- intent:
     * Encapsulate multiple conditions within parentheses in the resulting SQL,
     * allowing for complex logical grouping and order-of-operation control.
     *
     * @param Closure $callback A configuration closure receiving a fresh builder instance.
     * @param string  $boolean  The logical joiner for the entire nested group.
     * @return self A fresh, cloned builder instance containing the nested logical node.
     */
    protected function whereNested(Closure $callback, string $boolean = 'AND'): self
    {
        $query = $this->newQuery();

        $callback($query);

        $clone        = clone $this;
        $clone->state = $clone->state->addWhere(where: new NestedWhereNode(
            query: $query,
            boolean: $boolean
        ));

        $clone->state = $clone->state->mergeBindings(values: $query->state->getBindings());

        return $clone;
    }
}
