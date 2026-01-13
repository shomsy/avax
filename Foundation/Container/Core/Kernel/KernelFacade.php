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
 * Separated from orchestration to keep ContainerKernel focused on runtime resolution, enabling clean separation
 * between configuration and execution.
 *
 * @see docs/Core/Kernel/KernelFacade.md#quick-summary
 */
final readonly class KernelFacade
{
    /**
     * Initialize the facade with its collaborators.
     *
     * @param DefinitionStore         $definitions      Service registration store
     * @param ScopeManager            $scopes           Lifetime management system
     * @param ResolutionTimeline      $timeline         Resolution path tracker
     * @param ServicePrototypeFactory $prototypeFactory Reflection-based analyzer
     * @param CollectMetrics|null     $metrics          Performance metrics collector
     * @param TerminateContainer|null $terminator       Shutdown and cleanup handler
     * @param ContainerPolicy|null    $policy           Security and access policy
     *
     * @see docs/Core/Kernel/KernelFacade.md#method-__construct
     */
    public function __construct(
        private DefinitionStore        $definitions,
        private ScopeManager           $scopes,
        public ResolutionTimeline      $timeline,
        public ServicePrototypeFactory $prototypeFactory,
        public CollectMetrics|null     $metrics = null,
        public TerminateContainer|null $terminator = null,
        public ContainerPolicy|null    $policy = null
    ) {}

    /**
     * Bind an abstract to a concrete implementation.
     *
     * @param string               $abstract Abstract service identifier
     * @param string|callable|null $concrete Concrete implementation or factory
     * @param ServiceLifetime      $lifetime Service lifetime scope
     *
     * @return BindingBuilder Fluent binding builder for advanced configuration
     *
     * @see docs/Core/Kernel/KernelFacade.md#method-bind
     */
    public function bind(string $abstract, string|callable|null $concrete = null, ServiceLifetime $lifetime = ServiceLifetime::Transient) : BindingBuilder
    {
        return $this->bindAs(abstract: $abstract, concrete: $concrete, lifetime: $lifetime);
    }

    /**
     * Internal binding helper to create and store service definitions.
     *
     * @param string               $abstract Abstract service identifier
     * @param string|callable|null $concrete Concrete implementation or factory
     * @param ServiceLifetime      $lifetime Service lifetime scope
     *
     * @return BindingBuilder Fluent binding builder
     *
     * @see docs/Core/Kernel/KernelFacade.md#method-bindas
     */
    private function bindAs(string $abstract, string|callable|null $concrete, ServiceLifetime $lifetime) : BindingBuilder
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
     * @param string               $abstract Abstract service identifier
     * @param string|callable|null $concrete Concrete implementation or factory
     *
     * @return BindingBuilder Fluent binding builder for advanced configuration
     *
     * @see docs/Core/Kernel/KernelFacade.md#method-singleton
     */
    public function singleton(string $abstract, string|callable|null $concrete = null) : BindingBuilder
    {
        return $this->bindAs(abstract: $abstract, concrete: $concrete, lifetime: ServiceLifetime::Singleton);
    }

    /**
     * Bind an abstract as scoped (per request).
     *
     * @param string               $abstract Abstract service identifier
     * @param string|callable|null $concrete Concrete implementation or factory
     *
     * @return BindingBuilder Fluent binding builder for advanced configuration
     *
     * @see docs/Core/Kernel/KernelFacade.md#method-scoped
     */
    public function scoped(string $abstract, string|callable|null $concrete = null) : BindingBuilder
    {
        return $this->bindAs(abstract: $abstract, concrete: $concrete, lifetime: ServiceLifetime::Scoped);
    }

    /**
     * Extend an existing service with additional behavior.
     *
     * @param string   $abstract Abstract service identifier
     * @param callable $closure  Extension function that receives and returns the service instance
     *
     * @see docs/Core/Kernel/KernelFacade.md#method-extend
     */
    public function extend(string $abstract, callable $closure) : void
    {
        $extender = $closure instanceof Closure ? $closure : $closure(...);
        $this->definitions->addExtender(abstract: $abstract, extender: $extender);
    }

    /**
     * Register a resolving callback for a service or globally.
     *
     * @param string|callable $abstract Abstract identifier or global callback
     * @param callable|null   $callback Resolving callback (required if abstract is string)
     *
     * @throws InvalidArgumentException When parameters are invalid
     *
     * @see docs/Core/Kernel/KernelFacade.md#method-resolving
     */
    public function resolving(string|callable $abstract, callable|null $callback = null) : void
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
     * @param string $consumer Consumer class that requires the dependency
     *
     * @return ContextBuilder Contextual binding builder for fluent configuration
     *
     * @see docs/Core/Kernel/KernelFacade.md#method-when
     */
    public function when(string $consumer) : ContextBuilder
    {
        return new ContextBuilder(store: $this->definitions, consumer: $consumer);
    }

    /**
     * Register a singleton instance directly.
     *
     * @param string $abstract Abstract service identifier
     * @param object $instance Service instance to register
     *
     * @see docs/Core/Kernel/KernelFacade.md#method-instance
     */
    public function instance(string $abstract, object $instance) : void
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
     * @return DefinitionStore The definition store containing all service registrations
     *
     * @see docs/Core/Kernel/KernelFacade.md#method-definitions
     */
    public function definitions() : DefinitionStore
    {
        return $this->definitions;
    }

    /**
     * Get the scope manager.
     *
     * @return ScopeManager The scope manager handling service lifetime boundaries
     *
     * @see docs/Core/Kernel/KernelFacade.md#method-scopes
     */
    public function scopes() : ScopeManager
    {
        return $this->scopes;
    }
}
