<?php

declare(strict_types=1);

namespace Avax\Container\Runtime;

use Avax\Container\Compilation\CompiledContainer;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveRequest;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Avax\Container\Errors\ContainerException;
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
        $this->calls = [];
    }

    /**
     * Detaches the compiled runtime artifact.
     */
    public function detach() : void
    {
        $this->compiled = null;
        $this->calls = [];
    }

    /**
     * Reports whether a compiled runtime is attached.
     */
    public function isAttached() : bool
    {
        return $this->compiled !== null;
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

        $call = $this->calls[$method] ??= Closure::fromCallable([$this->compiled, $method]);

        return $call($resolver, $request, $request->overrides);
    }
}
