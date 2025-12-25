<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Closure;

/**
 * Trait HasControlStructures
 *
 * -- intent: extend the query builder with conditional execution pathways.
 */
trait HasControlStructures
{
    /**
     * Execute a callback if a given condition is falsy.
     *
     * -- intent: provide a pragmatic shorthand for negative conditional building.
     *
     * @param mixed         $condition Falsy value to evaluate
     * @param callable      $callback  Operation to perform if false
     * @param callable|null $default   Alternative operation if true
     *
     * @return QueryBuilder|HasControlStructures
     */
    public function unless(mixed $condition, callable $callback, callable|null $default = null) : self
    {
        return $this->when(condition: ! $condition, callback: $callback, default: $default);
    }

    /**
     * Execute a callback if a given truthy condition is met.
     *
     * -- intent: provide a pragmatic shorthand for conditional query building.
     *
     * @param mixed         $condition Boolean or truthy value to evaluate
     * @param callable      $callback  Operation to perform if true
     * @param callable|null $default   Alternative operation if false
     *
     * @return QueryBuilder|HasControlStructures
     */
    public function when(mixed $condition, callable $callback, callable|null $default = null) : self
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
     * Apply a closure directly to the query builder instance.
     *
     * -- intent: allow external logic to participate in the fluent chain.
     *
     * @param Closure $callback Operation to tap into
     *
     * @return QueryBuilder|HasControlStructures
     */
    public function tap(Closure $callback) : self
    {
        $callback($this);

        return $this;
    }
}
