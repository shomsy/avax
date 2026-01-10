<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Strategies;

use Avax\Container\Core\Kernel\Contracts\LifecycleStrategy;
use Avax\Container\Features\Operate\Scope\ScopeManager;

/**
 * Singleton Lifecycle Strategy
 *
 * @see docs/Core/Kernel/Strategies/SingletonLifecycleStrategy.md#quick-summary
 */
final readonly class SingletonLifecycleStrategy implements LifecycleStrategy
{
    /**
     * @param ScopeManager $scopeManager Scope storage used for singleton instances
     *
     * @see docs/Core/Kernel/Strategies/SingletonLifecycleStrategy.md#method-__constructscopemanager-scopemanager
     */
    public function __construct(
        private ScopeManager $scopeManager
    ) {}

    /**
     * Store a singleton instance for reuse.
     *
     * @param string $abstract Service identifier
     * @param mixed  $instance Instance to cache
     *
     * @return void
     * @see docs/Core/Kernel/Strategies/SingletonLifecycleStrategy.md#method-storestring-abstract-mixed-instance-void
     */
    public function store(string $abstract, mixed $instance): void
    {
        $this->scopeManager->set(abstract: $abstract, instance: $instance);
    }

    /**
     * Check if a singleton instance exists.
     *
     * @param string $abstract Service identifier
     *
     * @return bool True when cached
     * @see docs/Core/Kernel/Strategies/SingletonLifecycleStrategy.md#method-hasstring-abstract-bool
     */
    public function has(string $abstract): bool
    {
        return $this->scopeManager->has(abstract: $abstract);
    }

    /**
     * Retrieve a cached singleton instance.
     *
     * @param string $abstract Service identifier
     *
     * @return mixed|null Cached instance or null
     * @see docs/Core/Kernel/Strategies/SingletonLifecycleStrategy.md#method-retrievestring-abstract-mixed
     */
    public function retrieve(string $abstract): mixed
    {
        return $this->scopeManager->get(abstract: $abstract);
    }

    /**
     * No-op for singletons; retained for lifetime of the process.
     *
     * @return void
     * @see docs/Core/Kernel/Strategies/SingletonLifecycleStrategy.md#method-clearvoid
     */
    public function clear(): void {}
}
