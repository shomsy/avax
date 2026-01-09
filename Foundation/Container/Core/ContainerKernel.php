<?php

declare(strict_types=1);

namespace Avax\Container\Core;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\KernelCompiler;
use Avax\Container\Core\Kernel\KernelConfig;
use Avax\Container\Core\Kernel\KernelFacade;
use Avax\Container\Core\Kernel\KernelRuntime;
use Avax\Container\Core\Kernel\KernelState;
use Avax\Container\Core\Kernel\ResolutionPipelineBuilder;
use Avax\Container\Features\Actions\Advanced\Observe\Telemetry;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Core\DTO\InjectionReport;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Operate\Scope\ScopeManager;
use Avax\Container\Features\Think\Model\ServicePrototype;
use ReflectionClass;
use Throwable;

/**
 * Core runtime orchestrator for the container.
 *
 * Coordinates container operations through specialized components.
 * Acts as the central hub for resolution, scoping, and injection, orchestrating complex dependency management while maintaining clean separation of concerns.
 *
 * @see docs_md/Core/ContainerKernel.md#quick-summary
 */
final readonly class ContainerKernel implements ContainerInternalInterface
{
    private KernelRuntime  $runtime;
    private KernelState    $state;
    private KernelCompiler $compiler;
    private KernelFacade   $facade;

    /**
     * Initialize the kernel with core components.
     *
     * @param DefinitionStore $definitions
     * @param KernelConfig $config
     */
    public function __construct(
        private DefinitionStore $definitions,
        private KernelConfig    $config
    ) {
        $pipeline = ResolutionPipelineBuilder::defaultFromConfig(config: $config, definitions: $definitions);

        $this->runtime  = new KernelRuntime(pipeline: $pipeline, invoker: $config->invoker);
        $this->state    = new KernelState();
        $this->compiler = new KernelCompiler(definitions: $definitions, prototypeFactory: $config->prototypeFactory, metrics: $config->metrics);

        $this->facade = new KernelFacade(
            definitions: $definitions,
            scopes: $config->scopes,
            metrics: $config->metrics,
            timeline: $config->timeline,
            prototypeFactory: $config->prototypeFactory,
            terminator: $config->terminator,
            policy: $config->policy
        );
    }

    /**
     * Resolve a service with context.
     *
     * @param KernelContext $context
     * @return mixed
     * @throws ResolutionException
     * @see docs/Core/ContainerKernel.html
     */
    public function resolveContext(KernelContext $context): mixed
    {
        // Even with a context, if it's already in scope, we return it immediately
        if ($this->facade->scopes()->has(abstract: $context->serviceId)) {
            return $this->facade->scopes()->get(abstract: $context->serviceId);
        }

        return $this->runtime->resolveContext(context: $context);
    }

    /**
     * Check if a service is registered or can be resolved.
     *
     * @param string $id
     * @return bool
     * @see docs/Core/ContainerKernel.html
     */
    public function has(string $id): bool
    {
        if ($this->definitions->has(abstract: $id) || $this->facade->scopes()->has(abstract: $id)) {
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
     * @return ScopeManager
     * @see docs/Core/ContainerKernel.html
     */
    public function scopes(): ScopeManager
    {
        return $this->facade->scopes();
    }

    /**
     * Resolve a service by identifier.
     *
     * @param string $id
     * @return mixed
     * @throws ResolutionException
     * @throws ServiceNotFoundException
     * @see docs/Core/ContainerKernel.html
     */
    public function get(string $id): mixed
    {
        // O(1) Fast-track: check if already resolved in scopes
        if ($this->facade->scopes()->has(abstract: $id)) {
            return $this->facade->scopes()->get(abstract: $id);
        }

        return $this->runtime->get(id: $id);
    }

    /**
     * Register a shared instance.
     *
     * @param string $abstract
     * @param object $instance
     * @return void
     * @see docs/Core/ContainerKernel.html
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->facade->instance(abstract: $abstract, instance: $instance);
    }

    /**
     * Create a new instance with optional parameters.
     *
     * @param string $id
     * @param array $parameters
     * @return object
     * @throws ResolutionException
     * @throws ServiceNotFoundException
     * @see docs/Core/ContainerKernel.html
     */
    public function make(string $id, array $parameters = []): object
    {
        return $this->runtime->make(id: $id, parameters: $parameters);
    }

    /**
     * Resolve a service from a prototype.
     *
     * @param ServicePrototype $prototype
     * @return mixed
     * @throws ResolutionException
     * @see docs/Core/ContainerKernel.html
     */
    public function resolve(ServicePrototype $prototype): mixed
    {
        return $this->runtime->resolve(prototype: $prototype);
    }

    /**
     * Call a callable with dependency injection.
     *
     * @param callable|string $callable
     * @param array $parameters
     * @return mixed
     * @throws ResolutionException
     * @see docs/Core/ContainerKernel.html
     */
    public function call(callable|string $callable, array $parameters = []): mixed
    {
        return $this->runtime->call(callable: $callable, parameters: $parameters);
    }

    /**
     * Inject dependencies into an existing object.
     *
     * @param object $target
     * @return object
     * @throws ResolutionException
     * @see docs/Core/ContainerKernel.html
     */
    public function injectInto(object $target): object
    {
        return $this->runtime->injectInto(target: $target);
    }

    /**
     * Start a new resolution scope.
     *
     * @return void
     * @see docs/Core/ContainerKernel.html
     */
    public function beginScope(): void
    {
        $this->facade->scopes()->beginScope();
    }

    /**
     * End the current resolution scope.
     *
     * @return void
     * @see docs/Core/ContainerKernel.html
     */
    public function endScope(): void
    {
        $this->facade->scopes()->endScope();
    }

    /**
     * Check if an object can receive dependency injection.
     *
     * @param object $target
     * @return bool
     * @see docs/Core/ContainerKernel.html
     */
    public function canInject(object $target): bool
    {
        $report = $this->inspectInjection(target: $target);
        return !empty($report->properties) || !empty($report->methods);
    }

    /**
     * Inspect injection points on an object.
     *
     * @param object $target
     * @return InjectionReport
     * @see docs/Core/ContainerKernel.html
     */
    public function inspectInjection(object $target): InjectionReport
    {
        $class     = get_class($target);
        $prototype = $this->facade->prototypeFactory->createFor(class: $class);

        $properties = array_map(static fn($p) => $p->type ?? 'mixed', $prototype->injectedProperties);
        $methods = array_map(static fn($m) => array_map(static fn($pa) => $pa->type ?? 'mixed', $m->parameters), $prototype->injectedMethods);

        return new InjectionReport(
            success: !empty($properties) || !empty($methods),
            class: $class,
            properties: $properties,
            methods: $methods
        );
    }

    /**
     * Export metrics as string.
     *
     * @return string
     * @see docs/Core/ContainerKernel.html
     */
    public function exportMetrics(): string
    {
        return $this->telemetry()->exportMetrics();
    }

    /**
     * Get telemetry component.
     *
     * @return Telemetry
     */
    public function telemetry(): Telemetry
    {
        return $this->state->getOrInit(
            property: 'telemetry',
            factory: fn() => new Telemetry($this, $this->config->metrics)
        );
    }

    /**
     * Get the definition store.
     *
     * @return DefinitionStore
     * @see docs/Core/ContainerKernel.html
     */
    public function getDefinitions(): DefinitionStore
    {
        return $this->definitions;
    }
}
