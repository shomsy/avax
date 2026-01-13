<?php

declare(strict_types=1);

namespace Avax\Container\Features\Define\Bind;

use Avax\Container\Features\Core\Contracts\BindingBuilder as BindingBuilderInterface;
use Avax\Container\Features\Core\Contracts\ContextBuilder as ContextBuilderInterface;
use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\ServiceDefinition;

/**
 * Encapsulates service registration logic into the container's definition store.
 *
 * This class provides the canonical registration API used by developers to declare
 * service bindings, singletons, scoped services, and contextual injection rules.
 * It acts as the "Writer" to the {@see DefinitionStore}.
 *
 * @see     docs/Features/Define/Bind/Registrar.md
 */
readonly class Registrar
{
    /**
     * Initializes the registrar with a target storage.
     *
     * @param DefinitionStore $definitions The store where blueprints are saved.
     */
    public function __construct(private DefinitionStore $definitions) {}

    /**
     * Register a transient binding (new instance every time).
     *
     * @param string $abstract The service identifier.
     * @param mixed  $concrete The implementation (class name, closure, or null).
     *
     * @return BindingBuilderInterface Fluent builder for advanced config.
     *
     * @see docs/Features/Define/Bind/Registrar.md#method-bind
     */
    public function bind(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: ServiceLifetime::Transient);
    }

    /**
     * Internal helper to create and store blueprints.
     *
     * @param string          $abstract The service identifier.
     * @param mixed           $concrete The implementation.
     * @param ServiceLifetime $lifetime The resolution policy.
     *
     * @return BindingBuilderInterface The binding builder.
     */
    private function register(string $abstract, mixed $concrete, ServiceLifetime $lifetime) : BindingBuilderInterface
    {
        // Normalize concrete to abstract if null (self-binding)
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $definition           = new ServiceDefinition(abstract: $abstract);
        $definition->concrete = $concrete;
        $definition->lifetime = $lifetime;

        $this->definitions->add(definition: $definition);

        return new BindingBuilder(store: $this->definitions, abstract: $abstract);
    }

    /**
     * Register a singleton binding (shared instance).
     *
     * @param string $abstract The service identifier.
     * @param mixed  $concrete The implementation (class name, closure, or null).
     *
     * @return BindingBuilderInterface Fluent builder for advanced config.
     *
     * @see docs/Features/Define/Bind/Registrar.md#method-singleton
     */
    public function singleton(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: ServiceLifetime::Singleton);
    }

    /**
     * Register a scoped binding (shared within a request/task).
     *
     * @param string $abstract The service identifier.
     * @param mixed  $concrete The implementation (class name, closure, or null).
     *
     * @return BindingBuilderInterface Fluent builder for advanced config.
     *
     * @see docs/Features/Define/Bind/Registrar.md#method-scoped
     */
    public function scoped(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: ServiceLifetime::Scoped);
    }

    /**
     * Store an existing object instance as a singleton.
     *
     * @param string $abstract The service identifier.
     * @param object $instance The pre-built instance.
     *
     * @see docs/Features/Define/Bind/Registrar.md#method-instance
     */
    public function instance(string $abstract, object $instance) : void
    {
        $definition           = new ServiceDefinition(abstract: $abstract);
        $definition->concrete = $instance;
        $definition->lifetime = ServiceLifetime::Singleton;

        $this->definitions->add(definition: $definition);
    }

    /**
     * Register a post-resolution extender.
     *
     * @param string   $abstract The service identifier.
     * @param callable $closure  The extender callback.
     *
     * @see docs/Features/Define/Bind/Registrar.md#method-extend
     */
    public function extend(string $abstract, callable $closure) : void
    {
        $this->definitions->addExtender(abstract: $abstract, extender: $closure(...));
    }

    /**
     * Begin a contextual binding definition.
     *
     * @param string $consumer The class name that receives the dependency.
     *
     * @return ContextBuilderInterface Fluent builder for contextual rules.
     *
     * @see docs/Features/Define/Bind/Registrar.md#method-when
     */
    public function when(string $consumer) : ContextBuilderInterface
    {
        return new ContextBuilder(store: $this->definitions, consumer: $consumer);
    }

    /**
     * Assign tags to one or more services.
     *
     * @param string|string[] $abstracts Service identifiers.
     * @param string|string[] $tags      Tags to assign.
     *
     * @see docs/Features/Define/Bind/Registrar.md#method-tag
     */
    public function tag(string|array $abstracts, string|array $tags) : void
    {
        foreach ((array) $abstracts as $abstract) {
            $this->definitions->addTags(abstract: $abstract, tags: $tags);
        }
    }
}
