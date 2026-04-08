<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes;

use Avax\Container\Errors\ContainerException;

/**
 * Stores disposable instances for the current active scope stack.
 */
final class ScopeStore
{
    /** @var array<int, array<string, mixed>> */
    private array $scopes = [];

    /**
     * Returns whether the current scope already contains one service.
     */
    public function has(string $abstract) : bool
    {
        if ($this->scopes !== []) {
            $currentScope = $this->scopes[array_key_last($this->scopes)];
            return array_key_exists($abstract, $currentScope);
        }

        return false;
    }

    /**
     * Reads one scoped instance from the active scope.
     */
    public function get(string $abstract) : mixed
    {
        if ($this->scopes !== []) {
            $currentScope = $this->scopes[array_key_last($this->scopes)];
            if (array_key_exists($abstract, $currentScope)) {
                return $currentScope[$abstract];
            }
        }

        return null;
    }

    /**
     * Stores one instance in the active scope.
     *
     * @throws ContainerException
     */
    public function set(string $abstract, mixed $instance) : void
    {
        if ($this->scopes === []) {
            throw new ContainerException(message: 'Cannot store a scoped instance without an active scope.');
        }

        $lastIndex = array_key_last($this->scopes);
        $this->scopes[$lastIndex][$abstract] = $instance;
    }

    /**
     * Opens one new nested scope.
     */
    public function open() : void
    {
        $this->scopes[] = [];
    }

    /**
     * Closes the current nested scope.
     *
     * @throws ContainerException
     */
    public function close() : void
    {
        if ($this->scopes === []) {
            throw new ContainerException(message: 'Cannot close scope without an active scope.');
        }

        array_pop($this->scopes);
    }

    /**
     * Clears all active scopes.
     */
    public function terminate() : void
    {
        $this->scopes = [];
    }

    /**
     * @return array{scoped: array<int, array<string, mixed>>}
     */
    public function snapshot() : array
    {
        return [
            'scoped' => $this->scopes,
        ];
    }
}
