<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes;

use Avax\Container\Errors\ContainerException;

final class ScopeStore
{
    /** @var array<int, array<string, mixed>> */
    private array $scopes = [];

    public function has(string $abstract) : bool
    {
        if ($this->scopes !== []) {
            $currentScope = $this->scopes[array_key_last($this->scopes)];
            return array_key_exists($abstract, $currentScope);
        }

        return false;
    }

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

    public function set(string $abstract, mixed $instance) : void
    {
        if ($this->scopes === []) {
            throw new ContainerException(message: 'Cannot store a scoped instance without an active scope.');
        }

        $lastIndex = array_key_last($this->scopes);
        $this->scopes[$lastIndex][$abstract] = $instance;
    }

    public function open() : void
    {
        $this->scopes[] = [];
    }

    public function close() : void
    {
        if ($this->scopes === []) {
            throw new ContainerException(message: 'Cannot close scope without an active scope.');
        }

        array_pop($this->scopes);
    }

    public function terminate() : void
    {
        $this->scopes = [];
    }

}
