<?php

declare(strict_types=1);

namespace Avax\Container\Runtime;

use Avax\Container\Compilation\CompiledContainer;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveRequest;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Avax\Container\Errors\ContainerException;
use Closure;

final class HotPathInliner
{
    private CompiledContainer|null $compiled = null;

    /** @var array<string, Closure(ServiceResolver, ResolveRequest, array): mixed> */
    private array $calls = [];

    public function attach(CompiledContainer $compiled) : void
    {
        if ($this->compiled === $compiled) {
            return;
        }

        $this->compiled = $compiled;
        $this->calls = [];
    }

    public function detach() : void
    {
        $this->compiled = null;
        $this->calls = [];
    }

    public function isAttached() : bool
    {
        return $this->compiled !== null;
    }

    public function has(string $serviceId) : bool
    {
        return $this->compiled?->has(serviceId: $serviceId) ?? false;
    }

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
