<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime;

use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompiledContainer;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest;
use Closure;

/**
 * Dispatches compiled hot-path methods from the attached compiled container.
 */
final class HotPathInliner
{
    private CompiledContainer|null $compiledContainer = null;

    /** @var array<string, Closure(ResolveDependency, ResolveRequest, array) : mixed> */
    private array $calls = [];

    /**
     * Attaches one compiled runtime artifact.
     */
    public function attach(CompiledContainer $compiledContainer) : void
    {
        if ($this->compiledContainer === $compiledContainer) {
            return;
        }

        $this->compiledContainer = $compiledContainer;
        $this->calls    = [];
    }

    /**
     * Detaches the compiled runtime artifact.
     */
    public function detach() : void
    {
        $this->compiledContainer = null;
        $this->calls    = [];
    }

    /**
     * Reports whether a compiled runtime is attached.
     */
    public function isAttached() : bool
    {
        return $this->compiledContainer !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function state(string|null $serviceId = null) : array
    {
        $attached = $this->compiledContainer !== null;
        $hasEntry = $attached && is_string(value: $serviceId) && $serviceId !== '' && $this->compiledContainer->has(serviceId: $serviceId);

        return [
            'attached'   => $attached,
            'entryCount' => $this->compiledContainer?->entryCount() ?? 0,
            'entryIds'   => $this->compiledContainer?->entryIds() ?? [],
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
        return $this->compiledContainer?->has(serviceId: $serviceId) ?? false;
    }

    /**
     * Resolves one service through the compiled runtime.
     *
     * @throws ContainerException
     */
    public function resolve(string $serviceId, ResolveDependency $serviceResolver, ResolveRequest $resolveRequest) : mixed
    {
        if ($this->compiledContainer === null) {
            throw new ContainerException(message: 'HotPathInliner has no compiled runtime attached.');
        }

        $method = $this->compiledContainer->methodFor(serviceId: $serviceId);
        if ($method === null) {
            throw new ContainerException(message: sprintf('No compiled entry exists for [%s].', $serviceId));
        }

        $call = $this->calls[$method] ??= Closure::fromCallable(callback: [$this->compiledContainer, $method]);

        return $call($serviceResolver, $resolveRequest, $resolveRequest->overrides);
    }
}
