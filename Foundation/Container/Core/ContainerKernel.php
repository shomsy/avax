<?php

declare(strict_types=1);

namespace Avax\Container\Core;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\KernelConfig;
use Avax\Container\Core\Kernel\KernelFacade;
use Avax\Container\Core\Kernel\KernelRuntime;
use Avax\Container\Core\Kernel\KernelState;
use Avax\Container\Core\Kernel\ResolutionPipelineFactory;
use Avax\Container\Features\Actions\Advanced\Observe\Telemetry;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Core\DTO\InjectionReport;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Operate\Scope\ScopeManager;
use Avax\Container\Features\Think\Model\ServicePrototype;
use ReflectionClass;
use Throwable;

/**
 * Core runtime orchestrator for the container.
 *
 * Coordinates container operations through specialized components.
 * Acts as the central hub for resolution, scoping, and injection, orchestrating complex dependency management while
 * maintaining clean separation of concerns.
 *
 * @see      docs/Core/ContainerKernel.md#quick-summary
 *
 * @internal This class is not intended for public usage. Use ContainerInterface instead.
 */
final readonly class ContainerKernel implements ContainerInternalInterface
{
    private KernelRuntime $runtime;

    private KernelState $state;

    private KernelFacade $facade;

    /**
     * Initialize the kernel with core components.
     *
     *
     * @see docs/Core/ContainerKernel.md#method-__construct
     */
    public function __construct(
        private DefinitionStore $definitions,
        private KernelConfig    $config
    )
    {
        $pipeline = ResolutionPipelineFactory::defaultFromConfig(config: $config, definitions: $definitions);

        $this->runtime = new KernelRuntime(pipeline: $pipeline, invoker: $config->invoker);
        $this->state   = new KernelState;
        $this->facade  = new KernelFacade(
            definitions     : $definitions,
            scopes          : $config->scopes,
            timeline        : $config->timeline,
            prototypeFactory: $config->prototypeFactory,
            metrics         : $config->metrics,
            terminator      : $config->terminator,
            policy          : $config->policy
        );
    }

    /**
     * Resolve a service with context.
     *
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @throws \Throwable
     *
     * @see docs/Core/ContainerKernel.md#method-resolvecontext
     */
    public function resolveContext(KernelContext $context) : mixed
    {
        // Even with a context, if it's already in scope, we return it immediately
        if ($this->scopes()->has(abstract: $context->serviceId)) {
            return $this->scopes()->get(abstract: $context->serviceId);
        }

        return $this->runtime->resolveContext(context: $context);
    }

    /**
     * Check if a service is registered or can be resolved.
     *
     *
     * @see docs/Core/ContainerKernel.md#method-has
     */
    public function has(string $id) : bool
    {
        if ($this->definitions->has(abstract: $id) || $this->scopes()->has(abstract: $id)) {
            return true;
        }

        if (class_exists(class: $id)) {
            try {
                return (new ReflectionClass(objectOrClass: $id))->isInstantiable();
            } catch (Throwable) {
                return false;
            }
        }

        return false;
    }

    /**
     * Get the scope manager.
     *
     * @see docs/Core/ContainerKernel.md#method-scopes
     */
    public function scopes() : ScopeManager
    {
        return $this->facade->scopes();
    }

    /**
     * Resolve a service by identifier.
     *
     *
     * @param string $id
     *
     * @return mixed
     * @throws \Throwable
     * @see docs/Core/ContainerKernel.md#method-get
     */
    public function get(string $id) : mixed
    {
        // O(1) Fast-track: check if already resolved in scopes
        if ($this->scopes()->has(abstract: $id)) {
            return $this->scopes()->get(abstract: $id);
        }

        return $this->runtime->get(id: $id);
    }

    /**
     * Register a shared instance.
     *
     *
     * @see docs/Core/ContainerKernel.md#method-instance
     */
    public function instance(string $abstract, object $instance) : void
    {
        $this->facade->instance(abstract: $abstract, instance: $instance);
    }

    /**
     * Create a new instance with optional parameters.
     *
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @throws \Avax\Container\Features\Core\Exceptions\ServiceNotFoundException|\Throwable
     *
     * @see docs/Core/ContainerKernel.md#method-make
     */
    public function make(string $id, array $parameters = []) : object
    {
        return $this->runtime->make(id: $id, parameters: $parameters);
    }

    /**
     * Resolve a service from a prototype.
     *
     *
     * @param \Avax\Container\Features\Think\Model\ServicePrototype $prototype
     *
     * @return mixed
     * @throws \Throwable
     * @see docs/Core/ContainerKernel.md#method-resolve
     */
    public function resolve(ServicePrototype $prototype) : mixed
    {
        return $this->runtime->resolve(prototype: $prototype);
    }

    /**
     * Call a callable with dependency injection.
     *
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @throws \ReflectionException
     *
     * @see docs/Core/ContainerKernel.md#method-call
     */
    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->runtime->call(callable: $callable, parameters: $parameters);
    }

    /**
     * Inject dependencies into an existing object.
     *
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @throws \Throwable
     *
     * @see docs/Core/ContainerKernel.md#method-injectinto
     */
    public function injectInto(object $target) : object
    {
        return $this->runtime->injectInto(target: $target);
    }

    /**
     * Start a new resolution scope.
     *
     * @see docs/Core/ContainerKernel.md#method-beginscope
     */
    public function beginScope() : void
    {
        $this->scopes()->beginScope();
    }

    /**
     * End the current resolution scope.
     *
     * @see docs/Core/ContainerKernel.md#method-endscope
     */
    public function endScope() : void
    {
        $this->scopes()->endScope();
    }

    /**
     * Check if an object can receive dependency injection.
     *
     *
     * @see docs/Core/ContainerKernel.md#method-caninject
     */
    public function canInject(object $target) : bool
    {
        $report = $this->inspectInjection(target: $target);

        return ! empty($report->injectedProperties) || ! empty($report->injectedMethods);
    }

    /**
     * Inspect injection points on an object.
     *
     *
     * @see docs/Core/ContainerKernel.md#method-inspectinjection
     */
    public function inspectInjection(object|null $target = null) : InjectionReport
    {
        if ($target === null) {
            throw new ContainerException(message: 'inspectInjection requires a target object.');
        }

        $class     = $target::class;
        $prototype = $this->facade->prototypeFactory->createFor(class: $class);

        $properties = array_map(static fn($p) => $p->type ?? 'mixed', $prototype->injectedProperties);
        $methods    = array_map(static fn($m) => array_map(static fn($pa) => $pa->type ?? 'mixed', $m->parameters), $prototype->injectedMethods);

        return new InjectionReport(
            target            : $target,
            injectedProperties: $properties,
            injectedMethods   : $methods,
            success           : ! empty($properties) || ! empty($methods)
        );
    }

    /**
     * Export metrics as string.
     *
     * @see docs/Core/ContainerKernel.md#method-exportmetrics
     */
    public function exportMetrics() : string
    {
        return $this->telemetry()->exportMetrics();
    }

    /**
     * Get telemetry component.
     *
     * @see docs/Core/ContainerKernel.md#method-telemetry
     */
    public function telemetry() : Telemetry
    {
        return $this->state->getOrInit(
            property: 'telemetry',
            factory : fn() => new Telemetry(container: $this, metrics: $this->config->metrics)
        );
    }

    /**
     * Get the definition store.
     *
     * @see docs/Core/ContainerKernel.md#method-getdefinitions
     */
    public function getDefinitions() : DefinitionStore
    {
        return $this->definitions;
    }
}
