<?php

declare(strict_types=1);

namespace Avax\Container\Features\Define\Bind;

use Avax\Container\Features\Core\Contracts\BindingBuilder as BindingBuilderInterface;
use Avax\Container\Features\Core\Contracts\ContextBuilder as ContextBuilderInterface;
use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\ServiceDefinition;

/**
 * Encapsulates service registration into the container's definition store.
 *
 * This class provides the canonical “registration” API used by bootstrapping code:
 * bind/transient, singleton, scoped, instance binding, and contextual binding entry points.
 *
 * @see docs_md/Features/Define/Bind/Registrar.md#quick-summary
 */
class Registrar
{
    public function __construct(private DefinitionStore $definitions) {}

    /**
     * Register a transient binding.
     *
     * @param string $abstract Service id.
     * @param mixed $concrete Concrete class/factory or null.
     * @return BindingBuilderInterface
     * @see docs_md/Features/Define/Bind/Registrar.md#method-bind
     */
    public function bind(string $abstract, mixed $concrete = null): BindingBuilderInterface
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: ServiceLifetime::Transient);
    }

    /**
     * Register a singleton binding.
     *
     * @param string $abstract Service id.
     * @param mixed $concrete Concrete class/factory or null.
     * @return BindingBuilderInterface
     * @see docs_md/Features/Define/Bind/Registrar.md#method-singleton
     */
    public function singleton(string $abstract, mixed $concrete = null): BindingBuilderInterface
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: ServiceLifetime::Singleton);
    }

    /**
     * Register a scoped binding.
     *
     * @param string $abstract Service id.
     * @param mixed $concrete Concrete class/factory or null.
     * @return BindingBuilderInterface
     * @see docs_md/Features/Define/Bind/Registrar.md#method-scoped
     */
    public function scoped(string $abstract, mixed $concrete = null): BindingBuilderInterface
    {
        return $this->register(abstract: $abstract, concrete: $concrete, lifetime: ServiceLifetime::Scoped);
    }

    /**
     * Register a pre-built instance as a singleton definition.
     *
     * @param string $abstract Service id.
     * @param mixed $instance Instance to store.
     * @return void
     * @see docs_md/Features/Define/Bind/Registrar.md#method-instance
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $definition = new ServiceDefinition(abstract: $abstract);
        $definition->concrete = $instance;
        $definition->lifetime = ServiceLifetime::Singleton;
        $this->definitions->add(definition: $definition);
    }

    /**
     * Start defining a contextual binding rule for a consumer.
     *
     * @param string $consumer Consumer class name.
     * @return ContextBuilderInterface
     * @see docs_md/Features/Define/Bind/Registrar.md#method-when
     */
    public function when(string $consumer): ContextBuilderInterface
    {
        return new ContextBuilder(store: $this->definitions, consumer: $consumer);
    }

    private function register(string $abstract, mixed $concrete, ServiceLifetime $lifetime): BindingBuilderInterface
    {
        $definition = new ServiceDefinition(abstract: $abstract);
        $definition->concrete = $concrete;
        $definition->lifetime = $lifetime;
        $this->definitions->add(definition: $definition);

        return new BindingBuilder(store: $this->definitions, abstract: $abstract);
    }
}
