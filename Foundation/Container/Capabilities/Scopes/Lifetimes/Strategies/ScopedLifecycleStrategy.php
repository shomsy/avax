<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Scopes\Lifetimes\Strategies;

use Avax\Container\Capabilities\Scopes\Lifetimes\Contracts\LifecycleStrategy;
use Avax\Container\Capabilities\Scopes\ScopeManager;

/**
 * Scoped Lifecycle Strategy
 *
 */
final readonly class ScopedLifecycleStrategy implements LifecycleStrategy
{
    /**
     * @param ScopeManager $scopeManager Scoped storage manager
     *
     */
    public function __construct(
        private ScopeManager $scopeManager
    ) {}

    /**
     * Store an instance in the current scope.
     *
     * @param string $abstract Service identifier
     * @param mixed  $instance Instance to cache within scope
     *
     */
    public function store(string $abstract, mixed $instance) : void
    {
        $this->scopeManager->setScoped(abstract: $abstract, instance: $instance);
    }

    /**
     * Check if a scoped instance exists.
     *
     * @param string $abstract Service identifier
     *
     * @return bool True when cached in current scope
     *
     */
    public function has(string $abstract) : bool
    {
        return $this->scopeManager->has(abstract: $abstract);
    }

    /**
     * Retrieve a scoped instance.
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
     * Clear current scope instances.
     *
     */
    public function clear() : void
    {
        $this->scopeManager->endScope();
    }
}
