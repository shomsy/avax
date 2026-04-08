<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes;

use Avax\Container\Errors\ContainerException;

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
     *     disposable: array<string, bool>
     * }>
     */
    private array $scopes = [];

    /**
     * Returns whether the current scope already contains one service.
     */
    public function has(string $abstract) : bool
    {
        return $this->hasFor(abstract: $abstract);
    }

    /**
     * Reads one scoped instance from the active scope.
     */
    public function get(string $abstract) : mixed
    {
        return $this->getFor(abstract: $abstract);
    }

    /**
     * Stores one instance in the active scope.
     *
     * @throws ContainerException
     */
    public function set(string $abstract, mixed $instance, bool $disposable = false) : void
    {
        $this->setFor(abstract: $abstract, instance: $instance, disposable: $disposable);
    }

    /**
     * Opens one new nested scope.
     */
    public function open(string $kind = ScopeKind::OPERATION, string $scopeId = '') : void
    {
        $this->scopes[] = [
            'kind' => ScopeKind::normalize(kind: $kind),
            'id' => trim($scopeId),
            'items' => [],
            'disposable' => [],
        ];
    }

    /**
     * Closes the current nested scope.
     *
     * @throws ContainerException
     * @return array{
     *     kind: string,
     *     id: string,
     *     items: array<string, mixed>,
     *     disposable: array<string, bool>
     * }
     */
    public function close(string|null $kind = null) : array
    {
        if ($this->scopes === []) {
            throw new ContainerException(message: 'Cannot close scope without an active scope.');
        }

        $frame = $this->scopes[array_key_last($this->scopes)];
        if ($kind !== null && ScopeKind::normalize(kind: $kind) !== $frame['kind']) {
            throw new ContainerException(
                message: "Cannot close scope kind [{$kind}] while active scope kind [{$frame['kind']}] is on top of the stack."
            );
        }

        array_pop($this->scopes);

        return $frame;
    }

    /**
     * Clears all active scopes.
     */
    public function terminate() : array
    {
        $frames = $this->scopes;
        $this->scopes = [];

        return $frames;
    }

    public function hasActive(string $kind = ScopeKind::ANY) : bool
    {
        return $this->frameIndex(kind: $kind) !== null;
    }

    public function hasFor(string $abstract, string $kind = ScopeKind::ANY) : bool
    {
        $index = $this->frameIndex(kind: $kind);
        if ($index === null) {
            return false;
        }

        return array_key_exists($abstract, $this->scopes[$index]['items']);
    }

    public function getFor(string $abstract, string $kind = ScopeKind::ANY) : mixed
    {
        $index = $this->frameIndex(kind: $kind);
        if ($index === null) {
            return null;
        }

        return $this->scopes[$index]['items'][$abstract] ?? null;
    }

    /**
     * @throws ContainerException
     */
    public function setFor(
        string $abstract,
        mixed $instance,
        string $kind = ScopeKind::ANY,
        bool $disposable = false
    ) : void {
        $index = $this->frameIndex(kind: $kind);
        if ($index === null) {
            $required = ScopeKind::normalize(kind: $kind);
            $hint = $required === ScopeKind::ANY
                ? 'open a scope before resolving this service'
                : "open a [{$required}] scope before resolving this service";

            throw new ContainerException(
                message: "Cannot store scoped instance [{$abstract}] without an active matching scope; {$hint}."
            );
        }

        $this->scopes[$index]['items'][$abstract] = $instance;
        $this->scopes[$index]['disposable'][$abstract] = $disposable;
    }

    /**
     * @return array{
     *     scoped: array<int, array<string, mixed>>,
     *     frames: array<int, array{kind: string, id: string, services: list<string>}>
     * }
     */
    public function snapshot() : array
    {
        $frames = array_map(
            static function (array $frame) : array {
                $services = array_keys($frame['items']);
                sort($services);

                return [
                    'kind' => $frame['kind'],
                    'id' => $frame['id'],
                    'services' => $services,
                ];
            },
            $this->scopes
        );

        return [
            'scoped' => array_map(
                static fn(array $frame) : array => $frame['items'],
                $this->scopes
            ),
            'frames' => $frames,
        ];
    }

    private function frameIndex(string $kind) : int|null
    {
        if ($this->scopes === []) {
            return null;
        }

        $normalized = ScopeKind::normalize(kind: $kind);

        if ($normalized === ScopeKind::ANY) {
            return array_key_last($this->scopes);
        }

        for ($index = array_key_last($this->scopes); $index >= 0; $index--) {
            if (($this->scopes[$index]['kind'] ?? '') === $normalized) {
                return $index;
            }
        }

        return null;
    }
}
