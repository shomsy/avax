<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Scopes;

use Avax\Container\ScopeManagerInterface;

/**
 * Public-facing manager for container service scopes and shared instances.
 *
 * Direct management of the {@see ScopeRegistry} to provide a safe API for entering,
 * exiting, and clearing operational scopes across the application lifecycle.
 *
 */
final readonly class ScopeManager implements ScopeManagerInterface
{
    /**
     * Initializes the manager with a scope storage backend.
     *
     * @param ScopeRegistry $registry Underlying scope storage.
     *
     */
    public function __construct(private ScopeRegistry $registry) {}

    /**
     * Determine if a service instance is currently stored in active scopes or singletons.
     *
     * @param string $abstract The service identifier.
     *
     * @return bool True if an instance exists.
     *
     */
    public function has(string $abstract) : bool
    {
        return $this->registry->has(abstract: $abstract);
    }

    /**
     * Retrieve a resolved instance from the registry.
     *
     * @param string $abstract The service identifier.
     *
     * @return mixed|null The instance or null if not found.
     *
     */
    public function get(string $abstract) : mixed
    {
        return $this->registry->get(abstract: $abstract);
    }

    /**
     * Store an instance in the current active scope or singleton layer.
     *
     * @param string $abstract The service identifier.
     * @param mixed  $instance The object/instance to store.
     *
     */
    public function set(string $abstract, mixed $instance) : void
    {
        $this->registry->set(abstract: $abstract, instance: $instance);
    }

    /**
     * Compatibility alias for storing a global instance.
     *
     * @param string $abstract The service identifier.
     * @param mixed  $instance The object/instance to store globally.
     *
     */
    public function instance(string $abstract, mixed $instance) : void
    {
        $this->registry->addSingleton(abstract: $abstract, instance: $instance);
    }

    /**
     * Run a callable within a scope boundary.
     *
     * @template T
     *
     * @param callable():T $callback
     *
     * @return T
     */
    public function run(callable $callback) : mixed
    {
        $this->beginScope();

        try {
            return $callback();
        } finally {
            $this->endScope();
        }
    }

    /**
     * Create a new isolation scope.
     *
     */
    public function beginScope() : void
    {
        $this->registry->beginScope();
    }

    /**
     * Exit the current isolation scope, purging its instances.
     *
     */
    public function endScope() : void
    {
        $this->registry->endScope();
    }

    /**
     * Fully reset all shared state in the container.
     *
     */
    public function terminate() : void
    {
        $this->registry->terminate();
    }
}
