<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes;

use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;

/**
 * Stores disposable instances for the current active scope stack.
 */
final class ScopeStore
{
    /**
     * @var array<int, array{
     *     kind: string,
     *     id: string,
     *     items: array<string, mixed>,
     *     disposable: array<string, bool>,
     *     pooled: array<string, array{maxSize: int, resetBeforeReuse: bool, disposable: bool}>
     * }>
     */
    private array $scopes = [];

    /**
     * Returns whether the current scope already contains one service.
     */
    public function has(string $abstract): bool
    {
        return $this->hasFor(abstract: $abstract);
    }

    public function hasFor(string $abstract, string $kind = ScopeKind::Any->value): bool
    {
        $index = $this->frameIndex(kind: $kind);
        if ($index === null) {
            return false;
        }

        return array_key_exists(key: $abstract, array: $this->scopes[$index]['items']);
    }

    private function frameIndex(string $kind): ?int
    {
        if ($this->scopes === []) {
            return null;
        }

        $normalized = ScopeKind::normalize(kind: $kind)->value;

        if ($normalized === ScopeKind::Any->value) {
            return array_key_last(array: $this->scopes);
        }

        for ($index = array_key_last(array: $this->scopes); $index >= 0; $index--) {
            if (($this->scopes[$index]['kind'] ?? '') === $normalized) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Reads one scoped instance from the active scope.
     */
    public function get(string $abstract): mixed
    {
        return $this->getFor(abstract: $abstract);
    }

    public function getFor(string $abstract, string $kind = ScopeKind::Any->value): mixed
    {
        $index = $this->frameIndex(kind: $kind);
        if ($index === null) {
            return null;
        }

        return $this->scopes[$index]['items'][$abstract] ?? null;
    }

    /**
     * Stores one instance in the active scope.
     *
     * @throws ContainerException
     */
    public function set(string $abstract, mixed $instance, bool $disposable = false): void
    {
        $this->setFor(abstract: $abstract, instance: $instance, disposable: $disposable);
    }

    /**
     * @throws ContainerException
     */
    public function setFor(
        string $abstract,
        mixed $instance,
        ?string $kind = null,
        bool $disposable = false,
    ): void {
        $kind ??= ScopeKind::Any->value;
        $index = $this->frameIndex(kind: $kind);
        if ($index === null) {
            $required = ScopeKind::normalize(kind: $kind)->value;
            $hint = $required === ScopeKind::Any->value
                ? 'open a scope before resolving this service'
                : sprintf('open a [%s] scope before resolving this service', $required);

            throw new ContainerException(
                message: sprintf('Cannot store scoped instance [%s] without an active matching scope; %s.', $abstract, $hint),
            );
        }

        $this->scopes[$index]['items'][$abstract] = $instance;
        $this->scopes[$index]['disposable'][$abstract] = $disposable;
        unset($this->scopes[$index]['pooled'][$abstract]);
    }

    /**
     * Opens one new nested scope.
     */
    public function open(?string $kind = null, string $scopeId = ''): void
    {
        $kind ??= ScopeKind::Operation->value;
        $this->scopes[] = [
            'kind' => ScopeKind::normalize(kind: $kind)->value,
            'id' => trim(string: $scopeId),
            'items' => [],
            'disposable' => [],
            'pooled' => [],
        ];
    }

    /**
     * Closes the current nested scope.
     *
     * @return array{
     *     kind: string,
     *     id: string,
     *     items: array<string, mixed>,
     *     disposable: array<string, bool>,
     *     pooled: array<string, array{maxSize: int, resetBeforeReuse: bool, disposable: bool}>
     * }
     *
     * @throws ContainerException
     */
    public function close(?string $kind = null): array
    {
        if ($this->scopes === []) {
            throw new ContainerException(message: 'Cannot close scope without an active scope.');
        }

        $frame = $this->scopes[array_key_last(array: $this->scopes)];
        if ($kind !== null && ScopeKind::normalize(kind: $kind)->value !== $frame['kind']) {
            throw new ContainerException(
                message: sprintf('Cannot close scope kind [%s] while active scope kind [%s] is on top of the stack.', $kind, $frame['kind']),
            );
        }

        array_pop(array: $this->scopes);

        return $frame;
    }

    /**
     * Clears all active scopes.
     */
    public function terminate(): array
    {
        $frames = $this->scopes;
        $this->scopes = [];

        return $frames;
    }

    public function hasActive(string $kind = ScopeKind::Any->value): bool
    {
        return $this->frameIndex(kind: $kind) !== null;
    }

    /**
     * @throws ContainerException
     */
    public function setPooledFor(
        string $abstract,
        mixed $instance,
        string $kind,
        int $maxSize,
        ?bool $resetBeforeReuse = null,
        bool $disposable = false,
    ): void {
        $resetBeforeReuse ??= true;
        $index = $this->frameIndex(kind: $kind);
        if ($index === null) {
            $required = ScopeKind::normalize(kind: $kind)->value;

            throw new ContainerException(
                message: sprintf('Cannot checkout pooled instance [%s] without an active [%s] scope.', $abstract, $required),
            );
        }

        $this->scopes[$index]['items'][$abstract] = $instance;
        $this->scopes[$index]['disposable'][$abstract] = $disposable;
        $this->scopes[$index]['pooled'][$abstract] = [
            'maxSize' => max(1, $maxSize),
            'resetBeforeReuse' => $resetBeforeReuse,
            'disposable' => $disposable,
        ];
    }

    public function hasPooledFor(string $abstract, string $kind = ScopeKind::Any->value): bool
    {
        $index = $this->frameIndex(kind: $kind);
        if ($index === null) {
            return false;
        }

        return isset($this->scopes[$index]['pooled'][$abstract]);
    }

    /**
     * @return array{
     *     scoped: array<int, array<string, mixed>>,
     *     pooled: array<string, list<string>>,
     *     pooledStats: array<string, mixed>,
     *     frames: array<int, array{kind: string, id: string, services: list<string>, pooledServices: list<string>}>
     * }
     */
    public function snapshot(): array
    {
        $frames = array_map(
            callback: static function (array $frame): array {
                $services = array_keys(array: $frame['items']);
                sort(array: $services);
                $pooledServices = array_keys(array: $frame['pooled']);
                sort(array: $pooledServices);

                return [
                    'kind' => $frame['kind'],
                    'id' => $frame['id'],
                    'services' => $services,
                    'pooledServices' => $pooledServices,
                ];
            },
            array   : $this->scopes,
        );

        return [
            'scoped' => array_map(
                callback: static fn (array $frame): array => $frame['items'],
                array   : $this->scopes,
            ),
            'pooled' => array_reduce(
                array   : $this->scopes,
                callback: static function (array $carry, array $frame): array {
                    foreach (array_keys($frame['pooled']) as $serviceId) {
                        $carry[$serviceId][] = $frame['kind'].($frame['id'] !== '' ? ':'.$frame['id'] : '');
                    }

                    return $carry;
                },
                initial : [],
            ),
            'pooledStats' => [],
            'frames' => $frames,
        ];
    }
}
