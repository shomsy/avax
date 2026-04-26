<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Runtime;

use Avax\Container\DI\Capabilities\Composition\Compilation\CompiledContainer;
use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Container\DI\Capabilities\Resolution\ResolveRequest;
use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;
use Closure;

/**
 * Dispatches compiled hot-path methods from the attached compiled container.
 */
final class HotPathInliner
{
    private CompiledContainer|null $compiled = null;

    /** @var array<string, Closure(ServiceResolver, ResolveRequest, array): mixed> */
    private array $calls = [];

    /**
     * Attaches one compiled runtime artifact.
     */
    public function attach(CompiledContainer $compiled) : void
    {
        if ($this->compiled === $compiled) {
            return;
        }

        $this->compiled = $compiled;
        $this->calls    = [];
    }

    /**
     * Detaches the compiled runtime artifact.
     */
    public function detach() : void
    {
        $this->compiled = null;
        $this->calls    = [];
    }

    /**
     * Reports whether a compiled runtime is attached.
     */
    public function isAttached() : bool
    {
        return $this->compiled !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function state(string|null $serviceId = null) : array
    {
        $attached = $this->compiled !== null;
        $hasEntry = $attached && is_string(value: $serviceId) && $serviceId !== '' && $this->compiled->has(serviceId: $serviceId);

        return [
            'attached'   => $attached,
            'entryCount' => $this->compiled?->entryCount() ?? 0,
            'entryIds'   => $this->compiled?->entryIds() ?? [],
            'hasEntry'   => $hasEntry,
            'reason'     => match (true) {
                ! $attached                              => 'no compiled runtime is attached',
                $serviceId === null || $serviceId === '' => 'compiled runtime is attached',
                $hasEntry                                => 'compiled entry is attached',
                default                                  => 'compiled runtime is attached but the requested entry is missing',
            },
        ];
    }

    /**
     * Reports whether one compiled entry exists.
     */
    public function has(string $serviceId) : bool
    {
        return $this->compiled?->has(serviceId: $serviceId) ?? false;
    }

    /**
     * Resolves one service through the compiled runtime.
     *
     * @throws ContainerException
     */
    public function resolve(string $serviceId, ServiceResolver $resolver, ResolveRequest $request) : mixed
    {
        if ($this->compiled === null) {
            throw new ContainerException(message: 'HotPathInliner has no compiled runtime attached.');
        }

        $method = $this->compiled->methodFor(serviceId: $serviceId);
        if ($method === null) {
            throw new ContainerException(message: "No compiled entry exists for [{$serviceId}].");
        }

        $call = $this->calls[$method] ??= Closure::fromCallable(callback: [$this->compiled, $method]);

        return $call($resolver, $request, $request->overrides);
    }
}
