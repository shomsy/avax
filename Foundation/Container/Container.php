<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\Core\ContainerKernel;
use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Core\Contracts\BindingBuilder as BindingBuilderInterface;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Core\DTO\InjectionReport;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use Avax\Container\Features\Core\Exceptions\ServiceNotFoundException;
use Avax\Container\Features\Define\Bind\Registrar;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Operate\Scope\ScopeManager;
use Avax\Container\Features\Think\Model\ServicePrototype;

/**
 * The main entry point for dependency injection in your application.
 *
 * This class serves as the primary API for retrieving fully constructed objects from the dependency injection system.
 * It acts as a facade that hides the complex resolution machinery while providing a clean, simple interface for
 * service requests. By centralizing dependency management, it enables loose coupling, testability, and maintainability
 * across your entire application architecture.
 *
 * @see docs/Container.md#quick-summary
 */
final readonly class Container implements ContainerInternalInterface
{
    /**
     * Create a new Container instance.
     *
     * @param ContainerKernel $kernel The kernel that handles all container operations
     *
     * @see docs/Container.md#method-__construct
     */
    public function __construct(
        private ContainerKernel $kernel
    ) {}

    /**
     * Resolve a service by identifier.
     *
     * @param string $id Service identifier
     *
     * @return mixed The fully resolved service instance
     *
     * @throws ResolutionException When the service cannot be resolved due to dependency issues
     * @throws ServiceNotFoundException When the requested service identifier is not registered
     *
     * @see docs/Container.md#method-get
     */
    public function get(string $id) : mixed
    {
        return $this->kernel->get(id: $id);
    }

    /**
     * Check if a service is registered or can be resolved.
     *
     * @param string $id Service identifier to check
     *
     * @return bool True if the service can be resolved, false otherwise
     *
     * @see docs/Container.md#method-has
     */
    public function has(string $id) : bool
    {
        return $this->kernel->has(id: $id);
    }

    /**
     * Create a new instance with optional parameters.
     *
     * @param string $abstract   Service identifier or class name
     * @param array  $parameters Optional parameters to override constructor arguments
     *
     * @return object A new instance of the requested service
     *
     * @throws \Throwable
     * @see docs/Container.md#method-make
     */
    public function make(string $abstract, array $parameters = []) : object
    {
        return $this->kernel->make(id: $abstract, parameters: $parameters);
    }

    /**
     * Call a callable with dependency injection.
     *
     * @param callable|string $callable   The callable to execute
     * @param array           $parameters Additional parameters to pass to the callable
     *
     * @return mixed The result of the callable execution
     *
     * @throws \ReflectionException
     * @see docs/Container.md#method-call
     */
    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->kernel->call(callable: $callable, parameters: $parameters);
    }

    /**
     * Inject dependencies into an existing object.
     *
     * @param object $target The object to inject dependencies into
     *
     * @return object The same object with dependencies injected
     *
     * @throws \Throwable
     * @see docs/Container.md#method-injectinto
     */
    public function injectInto(object $target) : object
    {
        return $this->kernel->injectInto(target: $target);
    }

    /**
     * Resolve a service from a prototype.
     *
     * @param ServicePrototype $prototype Pre-analyzed service definition
     *
     * @return mixed The resolved service instance
     *
     * @throws ResolutionException When the prototype cannot be resolved
     *
     * @see docs/Container.md#method-resolve
     */
    public function resolve(ServicePrototype $prototype) : mixed
    {
        return $this->kernel->resolve(prototype: $prototype);
    }

    /**
     * Resolve a service with context.
     *
     * @param KernelContext $context Additional context for resolution
     *
     * @return mixed The resolved service instance
     *
     * @throws \Throwable
     * @see docs/Container.md#method-resolvecontext
     */
    public function resolveContext(KernelContext $context) : mixed
    {
        return $this->kernel->resolveContext(context: $context);
    }

    /**
     * Register a shared instance.
     *
     * @param string $abstract Service identifier for the instance
     * @param object $instance The object instance to register
     *
     * @see docs/Container.md#method-instance
     */
    public function instance(string $abstract, object $instance) : void
    {
        $this->kernel->instance(abstract: $abstract, instance: $instance);
    }

    /**
     * Start a new resolution scope.
     *
     * @see docs/Container.md#method-beginscope
     */
    public function beginScope() : void
    {
        $this->kernel->beginScope();
    }

    /**
     * End the current resolution scope.
     *
     * @see docs/Container.md#method-endscope
     */
    public function endScope() : void
    {
        $this->kernel->endScope();
    }

    /**
     * Check if an object can receive dependency injection.
     *
     * @param object $target The object to check for injection points
     *
     * @return bool True if the object has injection points, false otherwise
     *
     * @see docs/Container.md#method-caninject
     */
    public function canInject(object $target) : bool
    {
        $report = $this->inspectInjection(target: $target);

        return ! empty($report->injectedProperties) || ! empty($report->injectedMethods);
    }

    /**
     * Inspect injection points on an object.
     *
     * @param object|null $target The object to inspect for injection points
     *
     * @return InjectionReport Detailed report of injection points found
     *
     * @see docs/Container.md#method-inspectinjection
     */
    public function inspectInjection(object|null $target = null) : InjectionReport
    {
        if ($target === null) {
            throw new ContainerException(message: 'inspectInjection requires a target object.');
        }

        return $this->kernel->inspectInjection(target: $target);
    }

    /**
     * Get the scope manager.
     *
     * @return ScopeManager The scope manager for advanced scope control
     *
     * @see docs/Container.md#method-scopes
     */
    public function scopes() : ScopeManager
    {
        return $this->kernel->scopes();
    }

    /**
     * Export metrics as string.
     *
     * @return string Serialized metrics data for monitoring and analysis
     *
     * @see docs/Container.md#method-exportmetrics
     */
    public function exportMetrics() : string
    {
        return $this->kernel->exportMetrics();
    }

    /**
     * Bind a transient service definition.
     *
     * @param string $abstract Service identifier
     * @param mixed  $concrete Concrete implementation or null
     *
     * @return BindingBuilderInterface Fluent builder for additional config
     */
    public function bind(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        $registrar = new Registrar(definitions: $this->getDefinitions());

        return $registrar->bind(abstract: $abstract, concrete: $concrete);
    }

    /**
     * Get the definition store.
     *
     * @return DefinitionStore The store containing all service definitions
     *
     * @see docs/Container.md#related-files-folders
     */
    public function getDefinitions() : DefinitionStore
    {
        return $this->kernel->getDefinitions();
    }

    /**
     * Bind a singleton service definition.
     *
     * @param string $abstract Service identifier
     * @param mixed  $concrete Concrete implementation or null
     *
     * @return BindingBuilderInterface Fluent builder for additional config
     */
    public function singleton(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        $registrar = new Registrar(definitions: $this->getDefinitions());

        return $registrar->singleton(abstract: $abstract, concrete: $concrete);
    }
}
