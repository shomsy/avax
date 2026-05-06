<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes;

use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Components\Application\Container\System\Capabilities\Runtime\DependencyPool;
use Override;

/**
 * Owns shared and scoped instance lifecycle operations.
 */
final readonly class ManageScopes implements ScopeInterface
{
    public function __construct(private ScopeStore $scopeStore, private DependencyPool $dependencyPool, private DisposeInstances $disposeInstances = new DisposeInstances(), private ?ResolutionMetrics $resolutionMetrics = null)
    {
    }

    /**
     * Stores one shared instance.
     */
    #[Override]
    public function instance(string $abstract, mixed $instance): void
    {
        $this->dependencyPool->set(abstract: $abstract, instance: $instance);
    }

    /**
     * Stores one scoped instance.
     */
    #[Override]
    public function set(string $abstract, mixed $instance): void
    {
        $this->scopeStore->set(abstract: $abstract, instance: $instance);
    }

    /**
     * Runs one callback inside a temporary active scope.
     */
    #[Override]
    public function withinScope(callable $callback, string $kind = ScopeKind::OPERATION, string $scopeId = ''): mixed
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
    #[Override]
    public function openScope(string $kind = ScopeKind::OPERATION, string $scopeId = ''): void
    {
        $this->scopeStore->open(kind: $kind, scopeId: $scopeId);
        $this->resolutionMetrics?->increment(name: 'container_scope_open_total');
    }

    /**
     * Closes the current scope layer.
     */
    #[Override]
    public function closeScope(?string $kind = null): void
    {
        $frame = $this->scopeStore->close(kind: $kind);
        foreach ($frame['pooled'] as $serviceId => $options) {
            $instance = $frame['items'][$serviceId] ?? null;
            unset($frame['items'][$serviceId], $frame['disposable'][$serviceId]);

            $released = $this->dependencyPool->releasePooled(
                abstract        : $serviceId,
                instance        : $instance,
                maxSize         : $options['maxSize'],
                resetBeforeReuse: $options['resetBeforeReuse'],
                disposable      : $options['disposable'],
            );

            if (! ($released['returned'] ?? false)) {
                $this->disposeInstances?->dispose(
                    instance  : $instance,
                    disposable: $options['disposable'] || ($released['overflow'] ?? false) || ($released['unsafe'] ?? false),
                );
            }
        }

        $this->disposeInstances?->disposeMany(
            instances : $frame['items'],
            disposable: $frame['disposable'],
        );
        $this->resolutionMetrics?->increment(name: 'container_scope_close_total');
    }

    /**
     * Clears all shared and scoped runtime state.
     */
    #[Override]
    public function terminate(): void
    {
        $shared = $this->dependencyPool->drain();
        $this->disposeInstances?->disposeMany(instances: $shared['items'], disposable: $shared['disposable']);

        $pooled = $this->dependencyPool->drainPooled();
        foreach ($pooled['items'] as $serviceId => $bucket) {
            $options = $pooled['options'][$serviceId] ?? [
                'disposable' => false,
            ];

            foreach ($bucket as $instance) {
                $this->disposeInstances?->dispose(
                    instance  : $instance,
                    disposable: (bool) ($options['disposable'] ?? false),
                );
            }
        }

        $frames = $this->scopeStore->terminate();
        foreach (array_reverse(array: $frames) as $frame) {
            foreach ($frame['pooled'] as $serviceId => $options) {
                $instance = $frame['items'][$serviceId] ?? null;
                unset($frame['items'][$serviceId], $frame['disposable'][$serviceId]);

                $this->disposeInstances?->dispose(
                    instance  : $instance,
                    disposable: (bool) ($options['disposable'] ?? false),
                );
            }

            $this->disposeInstances?->disposeMany(
                instances : $frame['items'],
                disposable: $frame['disposable'],
            );
        }

        $this->resolutionMetrics?->increment(name: 'container_scope_terminate_total');
    }

    /**
     * @return array{
     *     shared: array<string, mixed>,
     *     scoped: array<int, array<string, mixed>>,
     *     pooled: array<string, list<string>>,
     *     pooledAvailable: array<string, list<string>>,
     *     pooledStats: array<string, int>,
     *     frames: array<int, array{kind: string, id: string, services: list<string>, pooledServices: list<string>}>
     * }
     */
    public function snapshot(): array
    {
        $snapshot = $this->scopeStore->snapshot();

        return [
            'shared' => $this->dependencyPool->snapshot(),
            'scoped' => $snapshot['scoped'],
            'pooled' => $snapshot['pooled'],
            'pooledAvailable' => $this->dependencyPool->pooledSnapshot(),
            'pooledStats' => $this->dependencyPool->pooledStats(),
            'frames' => $snapshot['frames'],
        ];
    }

    public function hasShared(string $abstract): bool
    {
        return $this->dependencyPool->has(abstract: $abstract);
    }

    /**
     * Returns whether one instance is available in shared or scoped storage.
     */
    #[Override]
    public function has(string $abstract): bool
    {
        if ($this->scopeStore->has(abstract: $abstract)) {
            return true;
        }

        return $this->dependencyPool->has(abstract: $abstract);
    }

    public function getShared(string $abstract): mixed
    {
        return $this->dependencyPool->get(abstract: $abstract);
    }

    /**
     * Reads one shared or scoped instance.
     */
    #[Override]
    public function get(string $abstract): mixed
    {
        if ($this->scopeStore->has(abstract: $abstract)) {
            return $this->scopeStore->get(abstract: $abstract);
        }

        return $this->dependencyPool->get(abstract: $abstract);
    }

    public function hasScoped(string $abstract, string $kind = ScopeKind::ANY): bool
    {
        return $this->scopeStore->hasFor(abstract: $abstract, kind: $kind);
    }

    public function getScoped(string $abstract, string $kind = ScopeKind::ANY): mixed
    {
        return $this->scopeStore->getFor(abstract: $abstract, kind: $kind);
    }

    public function setScoped(
        string $abstract,
        mixed $instance,
        ?string $kind = null,
        bool $disposable = false,
    ): void {
        $kind ??= ScopeKind::ANY;
        $this->scopeStore->setFor(
            abstract  : $abstract,
            instance  : $instance,
            kind      : $kind,
            disposable: $disposable,
        );
    }

    public function setShared(string $abstract, mixed $instance, bool $disposable = false): void
    {
        $this->dependencyPool->set(abstract: $abstract, instance: $instance, disposable: $disposable);
    }

    /**
     * @return array{hit: bool, instance: mixed}
     */
    public function checkoutPooled(
        string $abstract,
        string $kind,
        int $maxSize,
        ?bool $resetBeforeReuse = null,
        bool $disposable = false,
    ): array {
        $resetBeforeReuse ??= true;
        if ($this->scopeStore->hasPooledFor(abstract: $abstract, kind: $kind)) {
            return [
                'hit' => true,
                'instance' => $this->scopeStore->getFor(abstract: $abstract, kind: $kind),
            ];
        }

        $checkedOut = $this->dependencyPool->checkoutPooled(abstract: $abstract);
        if (! ($checkedOut['hit'] ?? false)) {
            return $checkedOut;
        }

        $this->scopeStore->setPooledFor(
            abstract        : $abstract,
            instance        : $checkedOut['instance'],
            kind            : $kind,
            maxSize         : $maxSize,
            resetBeforeReuse: $resetBeforeReuse,
            disposable      : $disposable,
        );

        return $checkedOut;
    }

    public function hasPooled(string $abstract, string $kind): bool
    {
        return $this->scopeStore->hasPooledFor(abstract: $abstract, kind: $kind);
    }

    public function getPooled(string $abstract, string $kind): mixed
    {
        return $this->scopeStore->getFor(abstract: $abstract, kind: $kind);
    }

    public function setPooled(
        string $abstract,
        mixed $instance,
        string $kind,
        int $maxSize,
        ?bool $resetBeforeReuse = null,
        bool $disposable = false,
    ): void {
        $resetBeforeReuse ??= true;
        $this->scopeStore->setPooledFor(
            abstract        : $abstract,
            instance        : $instance,
            kind            : $kind,
            maxSize         : $maxSize,
            resetBeforeReuse: $resetBeforeReuse,
            disposable      : $disposable,
        );
    }

    public function hasActiveScope(string $kind = ScopeKind::ANY): bool
    {
        return $this->scopeStore->hasActive(kind: $kind);
    }
}
