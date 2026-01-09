<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Features\Actions\Advanced\Observe\Telemetry;
use Closure;

/**
 * Kernel State - Lazy Instance Management for Runtime Flows.
 *
 * Manages lazy initialization of kernel components that are expensive to create
 * or should be instantiated only when needed. Provides a simple key-value store
 * with lazy factory support, optimizing startup performance and resource usage.
 *
 * @see docs_md/Core/Kernel/KernelState.md#quick-summary
 */
final class KernelState
{
    public Telemetry|null $telemetry = null;

    /**
     * Get or initialize a flow instance using the provided factory.
     *
     * Implements lazy initialization pattern for kernel state properties.
     * If the property is null, executes the factory and stores the result.
     * This enables just-in-time creation of expensive components.
     *
     * @param string $property Property name to get/initialize
     * @param Closure $factory Factory function to create the instance
     * @return mixed The property value
     * @throws \InvalidArgumentException If property doesn't exist on this class
     * @see docs_md/Core/Kernel/KernelState.md#method-getOrInit
     */
    public function getOrInit(string $property, Closure $factory): mixed
    {
        if (!property_exists($this, $property)) {
            throw new \InvalidArgumentException("Unknown state property: {$property}");
        }

        if ($this->$property === null) {
            $this->$property = $factory();
        }

        return $this->$property;
    }

    /**
     * Reset all lazy-initialized state to null.
     *
     * Forces re-initialization of all lazy components on next access.
     * Useful for testing or when you need to clear cached state.
     * This enables state cleanup and testing isolation.
     *
     * @return void
     * @see docs_md/Core/Kernel/KernelState.md#method-reset
     */
    public function reset(): void
    {
        $this->telemetry = null;
    }
}
