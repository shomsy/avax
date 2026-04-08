<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes;

use Avax\Container\Observability\ResolutionMetrics;
use Avax\Container\Runtime\ServicePool;

/**
 * Owns shared and scoped instance lifecycle operations.
 */
final readonly class ManageScopes implements ScopeInterface
{
    public function __construct(
        private ScopeStore $store,
        private ServicePool $pool,
        private ResolutionMetrics|null $metrics = null
    ) {}

    /**
     * Returns whether one instance is available in shared or scoped storage.
     */
    public function has(string $abstract) : bool
    {
        return $this->store->has(abstract: $abstract) || $this->pool->has(abstract: $abstract);
    }

    /**
     * Reads one shared or scoped instance.
     */
    public function get(string $abstract) : mixed
    {
        if ($this->store->has(abstract: $abstract)) {
            return $this->store->get(abstract: $abstract);
        }

        return $this->pool->get(abstract: $abstract);
    }

    /**
     * Stores one scoped instance.
     */
    public function set(string $abstract, mixed $instance) : void
    {
        $this->store->set(abstract: $abstract, instance: $instance);
    }

    /**
     * Stores one shared instance.
     */
    public function instance(string $abstract, mixed $instance) : void
    {
        $this->pool->set(abstract: $abstract, instance: $instance);
    }

    /**
     * Runs one callback inside a temporary active scope.
     */
    public function withinScope(callable $callback) : mixed
    {
        $this->openScope();

        try {
            return $callback();
        } finally {
            $this->closeScope();
        }
    }

    /**
     * Opens one new scope layer.
     */
    public function openScope() : void
    {
        $this->store->open();
        $this->metrics?->increment(name: 'container_scope_open_total');
    }

    /**
     * Closes the current scope layer.
     */
    public function closeScope() : void
    {
        $this->store->close();
        $this->metrics?->increment(name: 'container_scope_close_total');
    }

    /**
     * Clears all shared and scoped runtime state.
     */
    public function terminate() : void
    {
        $this->pool->flush();
        $this->store->terminate();
        $this->metrics?->increment(name: 'container_scope_terminate_total');
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
