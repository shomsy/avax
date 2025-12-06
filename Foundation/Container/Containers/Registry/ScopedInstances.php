<?php

declare(strict_types=1);

namespace Avax\Container\Containers\Registry;

use Avax\DataHandling\ArrayHandling\Arrhae;

/**
 * Manages scoped instance registry within the dependency injection container.
 *
 * This registry maintains a collection of instances that are bound to a specific
 * container scope. It provides functionality to manage the lifecycle of these
 * instances, ensuring proper scope isolation and memory management.
 *
 * @template TKey of string
 * @template TValue of object
 * @extends Arrhae<TKey, TValue>
 *
 * @immutable
 * @final
 */
class ScopedInstances extends Arrhae
{
    /**
     * Clears all registered instances from the current scope.
     *
     * This operation ensures proper cleanup of scoped instances by iterating
     * through all registered entries and removing them individually. This
     * method is crucial for preventing memory leaks when a scope is terminated.
     *
     * @throws \RuntimeException If unable to clear instances due to internal state corruption
     *
     */
    public function clear() : void
    {
        // Iterate through all registered instances in the current scope
        foreach ($this->all() as $key => $value) {
            // Remove each instance individually to ensure proper cleanup
            $this->forget($key);
        }
    }
}