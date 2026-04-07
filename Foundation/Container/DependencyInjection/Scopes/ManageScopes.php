<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes;

use Avax\Container\Runtime\ServicePool;

final readonly class ManageScopes implements ScopeInterface
{
    public function __construct(
        private ScopeStore $store,
        private ServicePool $pool
    ) {}

    public function has(string $abstract) : bool
    {
        return $this->store->has(abstract: $abstract) || $this->pool->has(abstract: $abstract);
    }

    public function get(string $abstract) : mixed
    {
        if ($this->store->has(abstract: $abstract)) {
            return $this->store->get(abstract: $abstract);
        }

        return $this->pool->get(abstract: $abstract);
    }

    public function set(string $abstract, mixed $instance) : void
    {
        $this->store->set(abstract: $abstract, instance: $instance);
    }

    public function instance(string $abstract, mixed $instance) : void
    {
        $this->pool->set(abstract: $abstract, instance: $instance);
    }

    public function withinScope(callable $callback) : mixed
    {
        $this->openScope();

        try {
            return $callback();
        } finally {
            $this->closeScope();
        }
    }

    public function openScope() : void
    {
        $this->store->open();
    }

    public function closeScope() : void
    {
        $this->store->close();
    }

    public function terminate() : void
    {
        $this->pool->flush();
        $this->store->terminate();
    }

    /**
     * @return array{shared: array<string, mixed>, scoped: array<int, array<string, mixed>>}
     */
    public function snapshot() : array
    {
        return [
            'shared' => $this->pool->snapshot(),
            'scoped' => $this->store->snapshot()['scoped'],
        ];
    }
}
