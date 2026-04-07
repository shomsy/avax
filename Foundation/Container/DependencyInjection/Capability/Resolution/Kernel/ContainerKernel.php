<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Kernel;

use Avax\Container\InjectionReport;
use Avax\Container\DependencyInjection\Capability\Definitions\Store\DefinitionStore;
use Avax\Container\DependencyInjection\Capability\Observability\Telemetry\Telemetry;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype;
use Avax\Container\DependencyInjection\Capability\Resolution\Pipeline\ResolutionPipelineFactory;
use Avax\Container\DependencyInjection\Capability\Scopes\ScopeManager;
use Avax\Container\DependencyInjection\Configuration\KernelConfig;

/**
 * Internal runtime owner for resolution, invocation, scopes, and diagnostics.
 */
final readonly class ContainerKernel
{
    private KernelRuntime $runtime;

    private KernelState $state;

    private KernelFacade $facade;

    public function __construct(
        private DefinitionStore $definitions,
        private KernelConfig    $config
    ) {
        $pipeline = ResolutionPipelineFactory::defaultFromConfig(
            config     : $config,
            definitions: $definitions
        );

        $this->runtime = new KernelRuntime(pipeline: $pipeline, invoker: $config->invoker);
        $this->state   = new KernelState;
        $this->facade  = new KernelFacade(
            definitions: $definitions,
            config     : $config,
            runtime    : $this->runtime,
            state      : $this->state
        );
    }

    public function resolveContext(KernelContext $context) : mixed
    {
        return $this->facade->resolveContext(context: $context);
    }

    public function has(string $id) : bool
    {
        return $this->facade->has(id: $id);
    }

    public function scopes() : ScopeManager
    {
        return $this->facade->scopes();
    }

    public function get(string $id) : mixed
    {
        return $this->facade->get(id: $id);
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->facade->instance(abstract: $abstract, instance: $instance);
    }

    public function make(string $id, array $parameters = []) : object
    {
        return $this->facade->make(id: $id, parameters: $parameters);
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->facade->call(callable: $callable, parameters: $parameters);
    }

    public function injectInto(object $target) : object
    {
        return $this->facade->injectInto(target: $target);
    }

    public function beginScope() : void
    {
        $this->facade->beginScope();
    }

    public function endScope() : void
    {
        $this->facade->endScope();
    }

    public function canInject(object $target) : bool
    {
        return $this->facade->canInject(target: $target);
    }

    public function inspectInjection(object|null $target = null) : InjectionReport
    {
        return $this->facade->inspectInjection(target: $target);
    }

    public function exportMetrics() : string
    {
        return $this->facade->exportMetrics();
    }

    public function telemetry() : Telemetry
    {
        return $this->facade->telemetry();
    }

    public function definitions() : DefinitionStore
    {
        return $this->facade->definitions();
    }

    public function resolve(ServicePrototype $prototype) : mixed
    {
        return $this->facade->resolve(prototype: $prototype);
    }
}
