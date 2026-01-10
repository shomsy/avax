<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Scope;

use LogicException;
use RuntimeException;

/**
 * Storage for resolved singleton and scoped service instances.
 *
 * This registry acts as the physical memory of the container's runtime, maintaining the
 * life cycle of shared instances across different operational scopes (e.g., Application,
 * Request, Session). It provides ordered scope management and isolation.
 *
 * @package Avax\Container\Features\Operate\Scope
 * @see docs/Features/Operate/Scope/ScopeRegistry.md
 */
final class ScopeRegistry
{
    /** @var array<string, mixed> Application-wide shared instances. */
    private array $singletons = [];

    /** @var array<int, array<string, mixed>> Stack of active operational scopes. */
    private array $scopes = [];

    /**
     * Check whether an instance exists in the current active scope or singleton storage.
     *
     * @param string $abstract The service identifier to check.
     * @return bool True if a resolved instance is stored.
     *
     * @see docs/Features/Operate/Scope/ScopeRegistry.md#method-has
     */
    public function has(string $abstract): bool
    {
        if ($this->scopes !== []) {
            $currentScope = $this->scopes[array_key_last($this->scopes)];
            if (isset($currentScope[$abstract])) {
                return true;
            }
        }

        return isset($this->singletons[$abstract]);
    }

    /**
     * Retrieve a resolved instance from the current active scope or singleton storage.
     *
     * @param string $abstract The service identifier to retrieve.
     * @return mixed|null The instance or null if not yet resolved.
     *
     * @see docs/Features/Operate/Scope/ScopeRegistry.md#method-get
     */
    public function get(string $abstract): mixed
    {
        if ($this->scopes !== []) {
            $currentScope = $this->scopes[array_key_last($this->scopes)];
            if (isset($currentScope[$abstract])) {
                return $currentScope[$abstract];
            }
        }

        return $this->singletons[$abstract] ?? null;
    }

    /**
     * Store a resolved instance into the current active storage.
     *
     * If a scope is currently active, the instance is stored in that scope.
     * Otherwise, it is stored in the application-wide singleton storage.
     *
     * @param string $abstract The service identifier.
     * @param mixed  $instance The resolved object/instance.
     *
     * @see docs/Features/Operate/Scope/ScopeRegistry.md#method-set
     */
    public function set(string $abstract, mixed $instance): void
    {
        if ($this->scopes !== []) {
            $lastIndex = array_key_last($this->scopes);
            $this->scopes[$lastIndex][$abstract] = $instance;
        } else {
            $this->singletons[$abstract] = $instance;
        }
    }

    /**
     * Force-store an instance into the application-wide singleton storage regardless of active scopes.
     *
     * @param string $abstract The service identifier.
     * @param mixed  $instance The instance to register globally.
     *
     * @see docs/Features/Operate/Scope/ScopeRegistry.md#method-addsingleton
     */
    public function addSingleton(string $abstract, mixed $instance): void
    {
        $this->singletons[$abstract] = $instance;
    }

    /**
     * Enter a new operational scope (e.g., start of a web request).
     *
     * @see docs/Features/Operate/Scope/ScopeRegistry.md#method-beginscope
     */
    public function beginScope(): void
    {
        $this->scopes[] = [];
    }

    /**
     * Exit the current operational scope and discard its stored instances.
     *
     * @throws RuntimeException If no active scope exists to end.
     * @see docs/Features/Operate/Scope/ScopeRegistry.md#method-endscope
     */
    public function endScope(): void
    {
        if ($this->scopes === []) {
            throw new RuntimeException(message: 'Cannot end scope: No active scope found.');
        }

        array_pop($this->scopes);
    }

    /**
     * Completely clear all singleton and scoped instances.
     *
     * @see docs/Features/Operate/Scope/ScopeRegistry.md#method-terminate
     */
    public function terminate(): void
    {
        $this->singletons = [];
        $this->scopes     = [];
    }
}
