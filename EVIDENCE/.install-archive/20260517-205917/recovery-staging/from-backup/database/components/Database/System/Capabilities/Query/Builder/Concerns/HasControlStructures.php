<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Builder\Concerns;

use Avax\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Closure;

/**
 * Trait providing programmatic branching and logical flow structures for the QueryBuilder.
 *
 * @see /docs/Foundation/Database/DSL/Transactions.md
 *
 * -- intent:
 * Extends the fluent QueryBuilder API with high-level control structures,
 * allowing for dynamic query building based on external application state
 * without breaking the continuous method chain.
 *
 * -- invariants:
 * - Callbacks must receive the current builder instance as their primary argument.
 * - Methods should return the resulting builder instance from the callback or
 *   the current instance if no modification occurred.
 * - Promotes a declarative style for complex, multi-path query construction.
 *
 * -- boundaries:
 * - Does NOT handle SQL compilation or state storage directly.
 * - Callbacks are responsible for cloning or mutation policies (typically builder methods handle cloning).
 */
trait HasControlStructures
{
    /**
     * Programmatic branch: execute a callback only if a specific condition is NOT met.
     *
     * -- intent:
     * Provides an expressive inverse of the when() method, typically used for
     *
     * applying default filters or     * @param mixed $condition Scalar, boolean, or truthy data point to evaluate.
     * @param  callable  $callback  The logic to execute if the condition evaluates to false.
     * @param  callable|null  $default  Optional alternative logic to execute if the condition is true.
     * @return QueryBuilder|HasControlStructures The
     *                                           resulting
     *                                           builder
     *                                           instance
     *                                           after
     *                                           applying
     *                                           the
     *                                           inverse
     *                                           conditional
     *                                           logic.
     *
     * @see
     * /docs/Foundation/Database/DSL/Transactions.md#transaction logic
     * when a specific flag is absent.
     */
    public function unless(mixed $condition, callable $callback, ?callable $default = null): self
    {
        return $this->when(condition: ! $condition, callback: $callback, default: $default);
    }

    /**
     * Programmatic branch: execute a callback only if a specific condition is met.
     *
     * -- intent:
     * Support dynamic query modification (e.g., adding filters based on user input)
     * by encapsulating the logic within a conditional fluently-chained block.
     *
     * @param  mixed  $condition  Scalar, boolean, or truthy data point to evaluate.
     * @param  callable  $callback  The logic to execute if the condition evaluates to true.
     * @param  callable|null  $default  Optional alternative logic to execute if the condition is false.
     * @return QueryBuilder|HasControlStructures The
     *                                           resulting
     *                                           builder
     *                                           instance
     *                                           after
     *                                           applying
     *                                           the
     *                                           conditional
     *                                           logic.
     */
    public function when(mixed $condition, callable $callback, ?callable $default = null): self
    {
        if ($condition) {
            return $callback($this, $condition) ?: $this;
        }

        if ($default !== null) {
            return $default($this, $condition) ?: $this;
        }

        return $this;
    }

    /**
     * Fluent diagnostic hook: execute a callback on the builder without modifying the chain return.
     *
     * -- intent:
     * Provide a mechanism for side-effects (logging, debugging, inspection)
     * within the fluent chain without requiring variable assignment.
     *
     * @param  Closure  $callback  A logic hook receiving the current builder instance.
     * @return QueryBuilder|HasControlStructures The
     *                                           current
     *                                           builder
     *                                           instance.
     */
    public function tap(Closure $callback): self
    {
        $callback($this);

        return $this;
    }
}
