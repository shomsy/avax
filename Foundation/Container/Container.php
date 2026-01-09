<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\Core\ContainerKernel;
use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Advanced\Observe\Telemetry;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Core\Contracts\ContextBuilder;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Operate\Scope\ScopeManager;
use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\Container\Features\Think\Prototype\ServicePrototypeFactory;
use Avax\Container\Observe\Timeline\ResolutionTimeline;
use Avax\Container\Features\Core\DTO\InjectionReport;

/**
 * The main entry point for dependency injection in your application.
 *
 * This class serves as the primary API for retrieving fully constructed objects from the dependency injection system. It acts as a facade that hides the complex resolution machinery while providing a clean, simple interface for service requests. By centralizing dependency management, it enables loose coupling, testability, and maintainability across your entire application architecture.
 *
 * @see docs_md/Container.md#quick-summary
 */
class Container implements ContainerInternalInterface
{
    /**
     * Create a new Container instance.
     *
     * @param ContainerKernel $kernel The kernel that handles all container operations
     */
    public function __construct(
        private readonly ContainerKernel $kernel
    ) {}

    /**
     * Resolve a service by identifier.
     *
     * This method is the main entry point for retrieving
     * fully constructed objects from the system. It orchestrates the complete resolution process, ensuring all dependencies are properly injected and the service is ready for use.
     *
     * @param string $id Service identifier
     * @return mixed The fully resolved service instance
     * @throws ResolutionException When the service cannot be resolved due to dependency issues
     * @throws ServiceNotFoundException When the requested service identifier is not registered
     * @see docs_md/Container.md#how-it-works-technical
     */
    public function get(string $id): mixed
    {
        return $this->kernel->get(id: $id);
    }

    /**
     * Check if a service is registered or can be resolved.
     *
     * This method performs a lightweight check to determine if a service identifier can be resolved without actually constructing the service instance. It's useful for conditional logic or defensive programming, allowing you to avoid exceptions in scenarios where service availability is uncertain.
     *
     * @param string $id Service identifier to check
     * @return bool True if the service can be resolved, false otherwise
     * @see docs_md/Container.md#how-it-works-technical
     */
    public function has(string $id): bool
    {
        return $this->kernel->has(id: $id);
    }

    /**
     * Create a new instance with optional parameters.
     *
     * This method always creates a new instance of the requested service, bypassing any scope caching. It's useful when you need multiple instances or want to override constructor parameters, providing fine-grained control over object creation.
     *
     * @param string $abstract Service identifier or class name
     * @param array $parameters Optional parameters to override constructor arguments
     * @return object A new instance of the requested service
     * @throws ResolutionException When dependencies cannot be resolved
     * @throws ServiceNotFoundException When the service is not registered
     * @see docs_md/Container.md#how-it-works-technical
     */
    public function make(string $abstract, array $parameters = []): object
    {
        return $this->kernel->make(id: $abstract, parameters: $parameters);
    }

    /**
     * Call a callable with dependency injection.
     *
     * This method executes a callable (function, method, or closure) with automatic dependency injection applied to its parameters. It's useful for event handlers, middleware, or legacy code that uses callables, enabling dependency injection in procedural code.
     *
     * @param callable|string $callable The callable to execute
     * @param array $parameters Additional parameters to pass to the callable
     * @return mixed The result of the callable execution
     * @throws ResolutionException When dependencies cannot be resolved for the callable
     * @see docs_md/Container.md#how-it-works-technical
     */
    public function call(callable|string $callable, array $parameters = []): mixed
    {
        return $this->kernel->call(callable: $callable, parameters: $parameters);
    }

    /**
     * Inject dependencies into an existing object.
     *
     * This method adds dependencies to an object that was created outside the container. It's useful for legacy objects, deserialization, or factory-created instances, allowing injection into objects not created by the container.
     *
     * @param object $target The object to inject dependencies into
     * @return object The same object with dependencies injected
     * @throws ResolutionException When dependencies cannot be resolved for injection
     * @see docs_md/Container.md#how-it-works-technical
     */
    public function injectInto(object $target): object
    {
        return $this->kernel->injectInto(target: $target);
    }

    /**
     * Resolve a service from a prototype.
     *
     * This method provides advanced API for performance optimization when you have pre-analyzed service definitions. It's used in advanced scenarios with prototype objects, bypassing standard resolution for pre-optimized cases.
     *
     * @param ServicePrototype $prototype Pre-analyzed service definition
     * @return mixed The resolved service instance
     * @throws ResolutionException When the prototype cannot be resolved
     * @see docs_md/Container.md#think-of-it
     */
    public function resolve(ServicePrototype $prototype): mixed
    {
        return $this->kernel->resolve(prototype: $prototype);
    }

    /**
     * Resolve a service with context.
     *
     * This method provides additional context information for complex resolution scenarios that require more than just the service ID, enabling advanced resolution logic based on runtime conditions.
     *
     * @param KernelContext $context Additional context for resolution
     * @return mixed The resolved service instance
     * @throws ResolutionException When the service cannot be resolved with the given context
     * @see docs_md/Container.md#architecture-role
     */
    public function resolveContext(KernelContext $context): mixed
    {
        return $this->kernel->resolveContext(context: $context);
    }

    /**
     * Register a shared instance.
     *
     * This method makes existing objects available through the container. It's useful for external objects or pre-configured services that should be accessible via the container, allowing integration of third-party or pre-built objects.
     *
     * @param string $abstract Service identifier for the instance
     * @param object $instance The object instance to register
     * @return void
     * @see docs_md/Container.md#how-it-works-technical
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->kernel->instance(abstract: $abstract, instance: $instance);
    }

    /**
     * Start a new resolution scope.
     *
     * This method creates a boundary for scoped service lifetimes, allowing services to have different lifetimes within the scope. Scopes enable proper resource management and prevent cross-request contamination.
     *
     * @return void
     * @see docs_md/Container.md#terminology
     */
    public function beginScope(): void
    {
        $this->kernel->beginScope();
    }

    /**
     * End the current resolution scope.
     *
     * This method cleans up scoped services and prevents memory leaks by disposing of instances that are no longer needed. It ensures proper resource cleanup when scope boundaries are exited.
     *
     * @return void
     * @see docs_md/Container.md#terminology
     */
    public function endScope(): void
    {
        $this->kernel->endScope();
    }

    /**
     * Check if an object can receive dependency injection.
     *
     * This method determines if an object has injection points (properties or methods marked for injection) without attempting to perform the injection. It's useful for conditional injection logic or validation.
     *
     * @param object $target The object to check for injection points
     * @return bool True if the object has injection points, false otherwise
     * @see docs_md/Container.md#how-it-works-technical
     */
    public function canInject(object $target): bool
    {
        $report = $this->inspectInjection(target: $target);

        return !empty($report->properties) || !empty($report->methods);
    }

    /**
     * Inspect injection points on an object.
     *
     * This method provides detailed information about what would be injected into an object, useful for debugging or tooling. It returns a report detailing properties and methods that can receive dependencies.
     *
     * @param object $target The object to inspect for injection points
     * @return InjectionReport Detailed report of injection points found
     * @see docs_md/Container.md#how-it-works-technical
     */
    public function inspectInjection(object $target): InjectionReport
    {
        return $this->kernel->inspectInjection(target: $target);
    }

    /**
     * Get the scope manager.
     *
     * This method provides direct access to scope management functionality for advanced scope manipulation. The scope manager handles nested scopes and complex lifetime scenarios.
     *
     * @return ScopeManager The scope manager for advanced scope control
     * @see docs_md/Container.md#related-files-folders
     */
    public function scopes(): ScopeManager
    {
        return $this->kernel->scopes();
    }

    /**
     * Export metrics as string.
     *
     * This method provides monitoring and performance analysis data about container usage. It exports comprehensive telemetry for analyzing resolution patterns, performance bottlenecks, and usage statistics.
     *
     * @return string Serialized metrics data for monitoring and analysis
     * @see docs_md/Container.md#risks-trade-offs-recommended-practices
     */
    public function exportMetrics(): string
    {
        return $this->kernel->exportMetrics();
    }

    /**
     * Get the definition store.
     *
     * This method provides access to the repository containing all service definitions and configuration. It's useful for introspection, debugging, and advanced service registration scenarios.
     *
     * @return DefinitionStore The store containing all service definitions
     * @see docs_md/Container.md#related-files-folders
     */
    public function getDefinitions(): DefinitionStore
    {
        return $this->kernel->getDefinitions();
    }
}
