<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Scopes\Lifetimes\Strategies;

use Avax\Container\Capabilities\Scopes\Lifetimes\Contracts\LifecycleStrategy;
use Avax\Container\Capabilities\Scopes\ScopeManager;

/**
 * Singleton Lifecycle Strategy
 *
 */
final readonly class SingletonLifecycleStrategy implements LifecycleStrategy
{
    /**
     * @param ScopeManager $scopeManager Scope storage used for singleton instances
     *
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
     */
    public function store(string $abstract, mixed $instance) : void
    {
        $this->scopeManager->set(abstract: $abstract, instance: $instance);
    }

    /**
     * Check if a singleton instance exists.
     *
     * @param string $abstract Service identifier
     *
     * @return bool True when cached
     *
     */
    public function has(string $abstract) : bool
    {
        return $this->scopeManager->has(abstract: $abstract);
    }

    /**
     * Retrieve a cached singleton instance.
     *
     * @param string $abstract Service identifier
     *
     * @return mixed|null Cached instance or null
     *
     */
    public function retrieve(string $abstract) : mixed
    {
        return $this->scopeManager->get(abstract: $abstract);
    }

    /**
     * No-op for singletons; retained for lifetime of the process.
     *
     */
    public function clear() : void {}
}
