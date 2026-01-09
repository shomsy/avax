<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Advanced\Lazy;

/**
 * Lazy Interface - Contract for Lazy Initialization
 *
 * Defines the contract for objects that support lazy initialization and deferred instantiation.
 * Implementations of this interface provide on-demand creation of expensive or rarely-used dependencies,
 * optimizing application startup performance and resource usage.
 *
 * @see docs_md/Features/Actions/Advanced/Lazy/LazyInterface.md#quick-summary
 */
interface LazyInterface
{
    /**
     * Get the lazily initialized value.
     *
     * Returns the wrapped object, creating it on first access if it hasn't been initialized yet.
     * Subsequent calls return the same cached instance.
     *
     * @return mixed The lazily initialized object instance
     * @see docs_md/Features/Actions/Advanced/Lazy/LazyInterface.md#method-get
     */
    public function get(): mixed;
}
