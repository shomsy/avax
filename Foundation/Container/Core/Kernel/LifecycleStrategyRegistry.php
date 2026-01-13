<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Core\Kernel\Contracts\LifecycleStrategy;
use InvalidArgumentException;

/**
 * Lifecycle Strategy Registry - Pluggable management of service lifecycles.
 *
 * Provides centralized storage and retrieval of lifecycle strategies, enabling extensible service lifetime management.
 *
 * @see docs/Core/Kernel/LifecycleStrategyRegistry.md#quick-summary
 */
final class LifecycleStrategyRegistry
{
    /** @var array<string, LifecycleStrategy> */
    private array $strategies = [];

    public function __construct(array $defaultStrategies = [])
    {
        foreach ($defaultStrategies as $name => $strategy) {
            $this->register(name: $name, strategy: $strategy);
        }
    }

    /**
     * Register a lifecycle strategy with a given name.
     *
     * Adds a new lifecycle strategy to the registry, making it available for resolution
     * by name. This enables extensible lifecycle management beyond built-in strategies.
     *
     * @param string            $name     Unique identifier for the strategy
     * @param LifecycleStrategy $strategy The strategy implementation to register
     *
     * @see docs/Core/Kernel/LifecycleStrategyRegistry.md#method-register
     */
    public function register(string $name, LifecycleStrategy $strategy) : void
    {
        $this->strategies[$name] = $strategy;
    }

    /**
     * Retrieve a lifecycle strategy by name.
     *
     * Gets the registered strategy implementation for the given name,
     * throwing an exception if the strategy is not found.
     *
     * @param string $name Strategy name to retrieve
     *
     * @return LifecycleStrategy The requested strategy implementation
     *
     * @throws InvalidArgumentException When strategy with given name is not registered
     *
     * @see docs/Core/Kernel/LifecycleStrategyRegistry.md#method-get
     */
    public function get(string $name) : LifecycleStrategy
    {
        if (! $this->has(name: $name)) {
            throw new InvalidArgumentException(message: "Lifecycle strategy [{$name}] not found.");
        }

        return $this->strategies[$name];
    }

    /**
     * Check if a lifecycle strategy exists by name.
     *
     * Determines whether a strategy with the given name has been registered,
     * enabling safe strategy lookups without throwing exceptions.
     *
     * @param string $name Strategy name to check for existence
     *
     * @return bool True if strategy exists, false otherwise
     *
     * @see docs/Core/Kernel/LifecycleStrategyRegistry.md#method-has
     */
    public function has(string $name) : bool
    {
        return isset($this->strategies[$name]);
    }

    /**
     * Get all registered lifecycle strategies.
     *
     * Returns a complete map of all registered strategies indexed by name,
     * enabling inspection and iteration over available lifecycle options.
     *
     * @return array<string, LifecycleStrategy> Map of strategy names to implementations
     *
     * @see docs/Core/Kernel/LifecycleStrategyRegistry.md#method-all
     */
    public function all() : array
    {
        return $this->strategies;
    }
}
