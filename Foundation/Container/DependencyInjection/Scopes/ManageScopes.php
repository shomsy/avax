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
    private DisposeInstances $disposer;

    public function __construct(
        private ScopeStore $store,
        private ServicePool $pool,
        DisposeInstances|null $disposer = null,
        private ResolutionMetrics|null $metrics = null
    ) {
        $this->disposer = $disposer ?? new DisposeInstances;
    }

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
    public function withinScope(callable $callback, string $kind = ScopeKind::OPERATION, string $scopeId = '') : mixed
    {
        $this->openScope(kind: $kind, scopeId: $scopeId);

        try {
            return $callback();
        } finally {
            $this->closeScope(kind: $kind);
        }
    }

    /**
     * Opens one new scope layer.
     */
    public function openScope(string $kind = ScopeKind::OPERATION, string $scopeId = '') : void
    {
        $this->store->open(kind: $kind, scopeId: $scopeId);
        $this->metrics?->increment(name: 'container_scope_open_total');
    }

    /**
     * Closes the current scope layer.
     */
    public function closeScope(string|null $kind = null) : void
    {
        $frame = $this->store->close(kind: $kind);
        $this->disposer?->disposeMany(
            instances  : $frame['items'],
            disposable : $frame['disposable']
        );
        $this->metrics?->increment(name: 'container_scope_close_total');
    }

    /**
     * Clears all shared and scoped runtime state.
     */
    public function terminate() : void
    {
        $shared = $this->pool->drain();
        $this->disposer?->disposeMany(instances: $shared['items'], disposable: $shared['disposable']);

        $frames = $this->store->terminate();
        foreach (array_reverse($frames) as $frame) {
            $this->disposer?->disposeMany(
                instances  : $frame['items'],
                disposable : $frame['disposable']
            );
        }

        $this->metrics?->increment(name: 'container_scope_terminate_total');
    }

    /**
     * @return array{
     *     shared: array<string, mixed>,
     *     scoped: array<int, array<string, mixed>>,
     *     frames: array<int, array{kind: string, id: string, services: list<string>}>
     * }
     */
    public function snapshot() : array
    {
        $snapshot = $this->store->snapshot();

        return [
            'shared' => $this->pool->snapshot(),
            'scoped' => $snapshot['scoped'],
            'frames' => $snapshot['frames'],
        ];
    }

    public function hasShared(string $abstract) : bool
    {
        return $this->pool->has(abstract: $abstract);
    }

    public function getShared(string $abstract) : mixed
    {
        return $this->pool->get(abstract: $abstract);
    }

    public function hasScoped(string $abstract, string $kind = ScopeKind::ANY) : bool
    {
        return $this->store->hasFor(abstract: $abstract, kind: $kind);
    }

    public function getScoped(string $abstract, string $kind = ScopeKind::ANY) : mixed
    {
        return $this->store->getFor(abstract: $abstract, kind: $kind);
    }

    public function setScoped(
        string $abstract,
        mixed $instance,
        string $kind = ScopeKind::ANY,
        bool $disposable = false
    ) : void {
        $this->store->setFor(
            abstract   : $abstract,
            instance   : $instance,
            kind       : $kind,
            disposable : $disposable
        );
    }

    public function setShared(string $abstract, mixed $instance, bool $disposable = false) : void
    {
        $this->pool->set(abstract: $abstract, instance: $instance, disposable: $disposable);
    }

    public function hasActiveScope(string $kind = ScopeKind::ANY) : bool
    {
        return $this->store->hasActive(kind: $kind);
    }
}
