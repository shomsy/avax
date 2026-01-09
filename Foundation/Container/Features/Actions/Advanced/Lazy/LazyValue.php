<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Advanced\Lazy;

use Closure;

/**
 * Lazy Value - Deferred Object Creation Wrapper
 *
 * Implements lazy initialization by wrapping a factory closure that creates expensive or rarely-used objects.
 * Defers object instantiation until first access, optimizing application startup performance and memory usage.
 * Once initialized, caches the created object for subsequent access.
 *
 * @see docs_md/Features/Actions/Advanced/Lazy/LazyValue.md#quick-summary
 */
final class LazyValue implements LazyInterface
{
    private bool  $initialized = false;
    private mixed $value       = null;

    /**
     * Create a new lazy value wrapper.
     *
     * @param Closure $factory Factory closure that creates the actual object when called
     * @see docs_md/Features/Actions/Advanced/Lazy/LazyValue.md#method-__construct
     */
    public function __construct(private readonly Closure $factory) {}

    /**
     * Get the lazily initialized value.
     *
     * Returns the wrapped object, creating it via the factory closure on first access.
     * Subsequent calls return the cached instance. If factory throws, marks as initialized
     * to prevent endless retries while preserving the exception.
     *
     * @return mixed The lazily initialized object instance
     * @throws \Throwable Any exception thrown by the factory closure during creation
     * @see docs_md/Features/Actions/Advanced/Lazy/LazyValue.md#method-get
     */
    public function get(): mixed
    {
        if (! $this->initialized) {
            try {
                $this->value = ($this->factory)();
            } finally {
                // Mark initialized to prevent endless retries even if factory throws.
                $this->initialized = true;
            }
        }

        return $this->value;
    }
}
