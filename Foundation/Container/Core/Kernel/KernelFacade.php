<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\Container\Features\Define\Bind\BindingBuilder;
use Avax\Container\Features\Define\Bind\ContextBuilder;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\ServiceDefinition;
use Avax\Container\Features\Operate\Scope\ScopeManager;
use Avax\Container\Features\Operate\Shutdown\TerminateContainer;
use Avax\Container\Features\Think\Prototype\ServicePrototypeFactory;
use Avax\Container\Guard\Rules\ContainerPolicy;
use Avax\Container\Observe\Metrics\CollectMetrics;
use Avax\Container\Observe\Timeline\ResolutionTimeline;
use Closure;
use InvalidArgumentException;

/**
 * Kernel Facade - Public API Layer for Service Registration
 *
 * Provides the user-facing API for binding services, singletons, extensions, etc.
 * Separated from orchestration to keep ContainerKernel focused on runtime resolution, enabling clean separation between configuration and execution.
 *
 * @see docs_md/Core/Kernel/KernelFacade.md#quick-summary
 */
final readonly class KernelFacade
{
    public function __construct(
        private DefinitionStore            $definitions,
        private ScopeManager               $scopes,
        public ResolutionTimeline          $timeline,
        public ServicePrototypeFactory     $prototypeFactory,
        public CollectMetrics|null         $metrics = null,
        public TerminateContainer|null     $terminator = null,
        public ContainerPolicy|null        $policy = null
    ) {}

    /**
     * Bind an abstract to a concrete implementation.
     *
     * Creates a service binding that associates an abstract identifier with a concrete implementation,
     * specifying the lifetime management behavior for resolved instances.
     *
     * @param string               $abstract Abstract service identifier
     * @param string|callable|null $concrete Concrete implementation or factory
     * @param ServiceLifetime      $lifetime Service lifetime scope
     *
     * @return BindingBuilder Fluent binding builder for advanced configuration
     * @see docs_md/Core/Kernel/KernelFacade.md#method-bind
     */
    public function bind(string $abstract, string|callable|null $concrete = null, ServiceLifetime $lifetime = ServiceLifetime::Transient): BindingBuilder
    {
        return $this->bindAs(abstract: $abstract, concrete: $concrete, lifetime: $lifetime);
    }

    /**
     * Internal binding helper.
     *
     * @param string               $abstract Abstract service identifier
     * @param string|callable|null $concrete Concrete implementation or factory
     * @param ServiceLifetime      $lifetime Service lifetime scope
     *
     * @return BindingBuilder Fluent binding builder
     */
    private function bindAs(string $abstract, string|callable|null $concrete, ServiceLifetime $lifetime): BindingBuilder
    {
        $definition           = new ServiceDefinition(abstract: $abstract);
        $definition->concrete = $concrete;
        $definition->lifetime = $lifetime;
        $this->definitions->add(definition: $definition);

        return new BindingBuilder(store: $this->definitions, abstract: $abstract);
    }

    /**
     * Bind an abstract as a singleton.
     *
     * Creates a singleton binding where the same instance is returned for all resolutions,
     * ensuring the service acts as a shared resource across the application.
     *
     * @param string               $abstract Abstract service identifier
     * @param string|callable|null $concrete Concrete implementation or factory
     *
     * @return BindingBuilder Fluent binding builder for advanced configuration
     * @see docs_md/Core/Kernel/KernelFacade.md#method-singleton
     */
    public function singleton(string $abstract, string|callable|null $concrete = null): BindingBuilder
    {
        return $this->bindAs(abstract: $abstract, concrete: $concrete, lifetime: ServiceLifetime::Singleton);
    }

    /**
     * Bind an abstract as scoped (per request).
     *
     * Creates a scoped binding where the same instance is shared within a resolution scope
     * (typically a single request), but different scopes get different instances.
     *
     * @param string               $abstract Abstract service identifier
     * @param string|callable|null $concrete Concrete implementation or factory
     *
     * @return BindingBuilder Fluent binding builder for advanced configuration
     * @see docs_md/Core/Kernel/KernelFacade.md#method-scoped
     */
    public function scoped(string $abstract, string|callable|null $concrete = null): BindingBuilder
    {
        return $this->bindAs(abstract: $abstract, concrete: $concrete, lifetime: ServiceLifetime::Scoped);
    }

    /**
     * Extend an existing service with additional behavior.
     *
     * Adds post-processing behavior to a service after it's resolved, allowing decoration
     * or modification of the service instance without changing the original binding.
     *
     * @param string   $abstract Abstract service identifier
     * @param callable $closure  Extension function that receives and returns the service instance
     *
     * @return void
     * @see docs_md/Core/Kernel/KernelFacade.md#method-extend
     */
    public function extend(string $abstract, callable $closure): void
    {
        $extender = $closure instanceof Closure ? $closure : $closure(...);
        $this->definitions->addExtender(abstract: $abstract, extender: $extender);
    }

    /**
     * Register a resolving callback for a service.
     *
     * Adds a callback that executes when a service is resolved, enabling service decoration,
     * initialization, or other post-resolution logic.
     *
     * @param string|callable $abstract Abstract identifier or global callback
     * @param callable|null   $callback Resolving callback (required if abstract is string)
     *
     * @return void
     * @throws InvalidArgumentException When parameters are invalid
     * @see docs_md/Core/Kernel/KernelFacade.md#method-resolving
     */
    public function resolving(string|callable $abstract, callable|null $callback = null): void
    {
        if (! is_string($abstract)) {
            if ($callback !== null) {
                throw new InvalidArgumentException(
                    message: 'resolving() accepts either (string $abstract, callable $callback) or a single callable (non-string) for global listeners.'
                );
            }

            $extender = $abstract instanceof Closure ? $abstract : $abstract(...);
            $this->definitions->addExtender(abstract: '*', extender: $extender);

            return;
        }

        if ($callback === null) {
            throw new InvalidArgumentException(
                message: 'resolving() requires a callback when the abstract is a string.'
            );
        }

        $extender = $callback instanceof Closure ? $callback : $callback(...);
        $this->definitions->addExtender(abstract: $abstract, extender: $extender);
    }

    /**
     * Create a contextual binding builder.
     *
     * Initiates contextual binding configuration where different implementations
     * can be bound based on the consuming class context.
     *
     * @param string $consumer Consumer class that requires the dependency
     *
     * @return ContextBuilder Contextual binding builder for fluent configuration
     * @see docs_md/Core/Kernel/KernelFacade.md#method-when
     */
    public function when(string $consumer): ContextBuilder
    {
        return new ContextBuilder(store: $this->definitions, consumer: $consumer);
    }

    /**
     * Register a singleton instance directly.
     *
     * Binds a pre-existing object instance as a singleton service, making it available
     * for injection while ensuring it's treated as a shared resource.
     *
     * @param string $abstract Abstract service identifier
     * @param object $instance Service instance to register
     *
     * @return void
     * @see docs_md/Core/Kernel/KernelFacade.md#method-instance
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->scopes->set(abstract: $abstract, instance: $instance);
        if (! $this->definitions->has(abstract: $abstract)) {
            $definition           = new ServiceDefinition(abstract: $abstract);
            $definition->lifetime = ServiceLifetime::Singleton;
            $this->definitions->add(definition: $definition);
        }
    }

    /**
     * Get the definition store.
     *
     * Provides direct access to the underlying service definition repository for advanced operations.
     *
     * @return DefinitionStore The definition store containing all service registrations
     * @see docs_md/Core/Kernel/KernelFacade.md#method-definitions
     */
    public function definitions(): DefinitionStore
    {
        return $this->definitions;
    }

    /**
     * Get the scope manager.
     *
     * Provides direct access to the scope management system for advanced scope operations.
     *
     * @return ScopeManager The scope manager handling service lifetime boundaries
     * @see docs_md/Core/Kernel/KernelFacade.md#method-scopes
     */
    public function scopes(): ScopeManager
    {
        return $this->scopes;
    }
}
