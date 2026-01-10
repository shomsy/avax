<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Contracts;

/**
 * Lifecycle Strategy Interface
 *
 * Defines how services are stored and retrieved based on their lifecycle.
 * Different strategies implement different caching behaviors (singleton, scoped, transient).
 *
 * @see docs/Core/Kernel/Contracts/LifecycleStrategy.md#quick-summary
 */
interface LifecycleStrategy
{
    /**
     * Store an instance according to the lifecycle strategy.
     *
     * How the instance is stored depends on the strategy:
     * - Singleton: Stored globally for the entire application
     * - Scoped: Stored within the current scope boundaries
     * - Transient: Not stored (no-op)
     *
     * @param string $abstract Service identifier
     * @param mixed $instance Service instance to store
     * @return void
     * @see docs/Core/Kernel/Contracts/LifecycleStrategy.md#method-store
     */
    public function store(string $abstract, mixed $instance): void;

    /**
     * Check if an instance exists for the given identifier.
     *
     * @param string $abstract Service identifier to check
     * @return bool True if instance exists and can be retrieved
     * @see docs/Core/Kernel/Contracts/LifecycleStrategy.md#method-has
     */
    public function has(string $abstract): bool;

    /**
     * Retrieve an instance for the given identifier.
     *
     * Returns the stored instance or null if not found.
     * For transient strategies, this always returns null.
     *
     * @param string $abstract Service identifier to retrieve
     * @return mixed Stored instance or null
     * @see docs/Core/Kernel/Contracts/LifecycleStrategy.md#method-retrieve
     */
    public function retrieve(string $abstract): mixed;

    /**
     * Clear stored instances.
     *
     * Removes all instances stored by this strategy.
     * For scoped strategies, this is called when scope ends.
     *
     * @return void
     * @see docs/Core/Kernel/Contracts/LifecycleStrategy.md#method-clear
     */
    public function clear(): void;
}
