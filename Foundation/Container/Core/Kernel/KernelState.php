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
 * @see docs/Core/Kernel/KernelState.md#quick-summary
 */
final class KernelState
{
    /** @var Telemetry|null The telemetry collector instance. */
    public Telemetry|null $telemetry = null;

    /**
     * Get or initialize a flow instance using the provided factory.
     *
     * @param string  $property Property name to get/initialize.
     * @param Closure $factory  Factory function to create the instance.
     * @return mixed The property value.
     * @throws \InvalidArgumentException If property doesn't exist on this class.
     * @see docs/Core/Kernel/KernelState.md#method-getorinit
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
     * @return void
     * @see docs/Core/Kernel/KernelState.md#method-reset
     */
    public function reset(): void
    {
        $this->telemetry = null;
    }
}
