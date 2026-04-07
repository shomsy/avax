<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel;

use Avax\Container\DependencyInjection\Capabilities\Observability\Telemetry\Telemetry;
use Closure;
use InvalidArgumentException;

/**
 * Kernel State - Lazy Instance Management for Runtime Flows.
 *
 * Manages lazy initialization of kernel components that are expensive to create
 * or should be instantiated only when needed. Provides a simple key-value store
 * with lazy factory support, optimizing startup performance and resource usage.
 *
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
     *
     * @return mixed The property value.
     *
     * @throws \InvalidArgumentException If property doesn't exist on this class.
     *
     */
    public function getOrInit(string $property, Closure $factory) : mixed
    {
        if (! property_exists($this, $property)) {
            throw new InvalidArgumentException(message: "Unknown state property: {$property}");
        }

        if ($this->$property === null) {
            $this->$property = $factory();
        }

        return $this->$property;
    }

    /**
     * Reset all lazy-initialized state to null.
     *
     */
    public function reset() : void
    {
        $this->telemetry = null;
    }
}
