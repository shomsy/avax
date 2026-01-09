<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Scope;

use LogicException;
use RuntimeException;

/**
 * Holds resolved instances in singleton and scoped storage.
 *
 * @see docs_md/Features/Operate/Scope/ScopeRegistry.md#quick-summary
 */
final class ScopeRegistry
{
    /** @var array<string, mixed> */
    private array $singletons = [];

    /** @var array<int, array<string, mixed>> */
    private array $scopes = [];

    /**
     * Check whether an instance exists in the current scope (if active) or singleton storage.
     *
     * @see docs_md/Features/Operate/Scope/ScopeRegistry.md#method-has
     */
    public function has(string $abstract): bool
    {
        if ($this->scopes !== []) {
            $currentScope = end($this->scopes);
            if (isset($currentScope[$abstract])) {
                return true;
            }
        }

        return isset($this->singletons[$abstract]);
    }

    /**
     * Retrieve an instance from the current scope (if active) or singleton storage.
     *
     * @see docs_md/Features/Operate/Scope/ScopeRegistry.md#method-get
     */
    public function get(string $abstract): mixed
    {
        if ($this->scopes !== []) {
            $currentScope = end($this->scopes);
            if (isset($currentScope[$abstract])) {
                return $currentScope[$abstract];
            }
        }

        return $this->singletons[$abstract] ?? null;
    }

    /**
     * Store an instance in singleton storage.
     *
     * @see docs_md/Features/Operate/Scope/ScopeRegistry.md#method-set
     */
    public function set(string $abstract, mixed $instance): void
    {
        $this->singletons[$abstract] = $instance;
    }

    /**
     * Store an instance in the currently active scope.
     *
     * @throws RuntimeException If no scope is active.
     * @see docs_md/Features/Operate/Scope/ScopeRegistry.md#method-setscoped
     */
    public function setScoped(string $abstract, mixed $instance): void
    {
        if ($this->scopes === []) {
            throw new RuntimeException(message: "Cannot cache scoped service [{$abstract}] without an active scope.");
        }
        $key                           = array_key_last($this->scopes);
        $this->scopes[$key][$abstract] = $instance;
    }

    /**
     * Begin a new scope boundary.
     *
     * @see docs_md/Features/Operate/Scope/ScopeRegistry.md#method-beginscope
     */
    public function beginScope(): void
    {
        $this->scopes[] = [];
    }

    /**
     * End the current scope boundary.
     *
     * @throws LogicException If no scope is active.
     * @see docs_md/Features/Operate/Scope/ScopeRegistry.md#method-endscope
     */
    public function endScope(): void
    {
        if ($this->scopes === []) {
            throw new LogicException(message: "No active scopes to end.");
        }
        array_pop($this->scopes);
    }

    /**
     * Terminate the scope system and clear all stored instances.
     *
     * @see docs_md/Features/Operate/Scope/ScopeRegistry.md#method-terminate
     */
    public function terminate(): void
    {
        $this->clear();
    }

    /**
     * Clear all singleton and scoped instances.
     *
     * @see docs_md/Features/Operate/Scope/ScopeRegistry.md#method-clear
     */
    public function clear(): void
    {
        $this->singletons = [];
        $this->scopes     = [];
    }
}
