<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Strategies;

use Avax\Container\Core\Kernel\Contracts\LifecycleStrategy;
use Avax\Container\Features\Operate\Scope\ScopeManager;

/**
 * Scoped Lifecycle Strategy
 *
 * @see docs_md/Core/Kernel/Strategies/ScopedLifecycleStrategy.md#quick-summary
 */
final readonly class ScopedLifecycleStrategy implements LifecycleStrategy
{
    /**
     * @param ScopeManager $scopeManager Scoped storage manager
     *
     * @see docs_md/Core/Kernel/Strategies/ScopedLifecycleStrategy.md#method-__constructscopemanager-scopemanager
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
     * @return void
     * @see docs_md/Core/Kernel/Strategies/ScopedLifecycleStrategy.md#method-storestring-abstract-mixed-instance-void
     */
    public function store(string $abstract, mixed $instance): void
    {
        $this->scopeManager->setScoped(abstract: $abstract, instance: $instance);
    }

    /**
     * Check if a scoped instance exists.
     *
     * @param string $abstract Service identifier
     *
     * @return bool True when cached in current scope
     * @see docs_md/Core/Kernel/Strategies/ScopedLifecycleStrategy.md#method-hasstring-abstract-bool
     */
    public function has(string $abstract): bool
    {
        return $this->scopeManager->has(abstract: $abstract);
    }

    /**
     * Retrieve a scoped instance.
     *
     * @param string $abstract Service identifier
     *
     * @return mixed|null Cached instance or null
     * @see docs_md/Core/Kernel/Strategies/ScopedLifecycleStrategy.md#method-retrievestring-abstract-mixed
     */
    public function retrieve(string $abstract): mixed
    {
        return $this->scopeManager->get(abstract: $abstract);
    }

    /**
     * Clear current scope instances.
     *
     * @return void
     * @see docs_md/Core/Kernel/Strategies/ScopedLifecycleStrategy.md#method-clearvoid
     */
    public function clear(): void
    {
        $this->scopeManager->endScope();
    }
}
