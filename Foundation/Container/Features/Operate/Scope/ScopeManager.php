<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Scope;

/**
 * Public-facing manager for container service scopes and shared instances.
 *
 * Direct management of the {@see ScopeRegistry} to provide a safe API for entering, 
 * exiting, and clearing operational scopes across the application lifecycle.
 *
 * @package Avax\Container\Features\Operate\Scope
 * @see docs/Features/Operate/Scope/ScopeManager.md
 */
final readonly class ScopeManager
{
    /**
     * Initializes the manager with a scope storage backend.
     *
     * @param ScopeRegistry $registry Underlying scope storage.
     * @see docs/Features/Operate/Scope/ScopeManager.md#method-__construct
     */
    public function __construct(private ScopeRegistry $registry) {}

    /**
     * Determine if a service instance is currently stored in active scopes or singletons.
     *
     * @param string $abstract The service identifier.
     * @return bool True if an instance exists.
     * @see docs/Features/Operate/Scope/ScopeManager.md#method-has
     */
    public function has(string $abstract): bool
    {
        return $this->registry->has(abstract: $abstract);
    }

    /**
     * Retrieve a resolved instance from the registry.
     *
     * @param string $abstract The service identifier.
     * @return mixed|null The instance or null if not found.
     * @see docs/Features/Operate/Scope/ScopeManager.md#method-get
     */
    public function get(string $abstract): mixed
    {
        return $this->registry->get(abstract: $abstract);
    }

    /**
     * Store an instance in the current active scope or singleton layer.
     *
     * @param string $abstract The service identifier.
     * @param mixed  $instance The object/instance to store.
     * @see docs/Features/Operate/Scope/ScopeManager.md#method-set
     */
    public function set(string $abstract, mixed $instance): void
    {
        $this->registry->set(abstract: $abstract, instance: $instance);
    }

    /**
     * Compatibility alias for storing a global instance.
     *
     * @param string $abstract The service identifier.
     * @param mixed  $instance The object/instance to store globally.
     * @see docs/Features/Operate/Scope/ScopeManager.md#method-instance
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->registry->addSingleton(abstract: $abstract, instance: $instance);
    }

    /**
     * Create a new isolation scope.
     *
     * @see docs/Features/Operate/Scope/ScopeManager.md#method-beginscope
     */
    public function beginScope(): void
    {
        $this->registry->beginScope();
    }

    /**
     * Exit the current isolation scope, purging its instances.
     *
     * @see docs/Features/Operate/Scope/ScopeManager.md#method-endscope
     */
    public function endScope(): void
    {
        $this->registry->endScope();
    }

    /**
     * Fully reset all shared state in the container.
     *
     * @see docs/Features/Operate/Scope/ScopeManager.md#method-terminate
     */
    public function terminate(): void
    {
        $this->registry->terminate();
    }
}
