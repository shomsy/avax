<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Definitions\Bindings;

use Avax\Container\DependencyInjection\Capability\Definitions\Contracts\BindingBuilderInterface as BindingBuilderContract;
use Avax\Container\DependencyInjection\Capability\Definitions\Store\DefinitionStore;
use Avax\Container\DependencyInjection\Capability\Definitions\Store\ServiceDefinition;

/**
 * Fluent API Builder for configuring service bindings.
 *
 * This class provides a Domain-Specific Language (DSL) for decorating and refining
 * service blueprints. It enables developers to specify concrete implementations,
 * add category tags, and provide manual constructor argument overrides.
 *
 */
readonly class BindingBuilder implements BindingBuilderContract
{
    /**
     * Initializes a new builder instance.
     *
     * @param DefinitionStore $store    The registry where the definition is persisted.
     * @param string          $abstract The identifier of the service being configured.
     *
     */
    public function __construct(
        private DefinitionStore $store,
        private string          $abstract
    ) {}

    /**
     * Specify the concrete implementation for this binding.
     *
     * @param string|callable|null $concrete Class name, closure, or specific instance.
     *
     * @return self Filtered builder for fluent chaining.
     *
     */
    public function to(string|callable|null $concrete) : self
    {
        $definition           = $this->getDefinition();
        $definition->concrete = $concrete;

        return $this;
    }

    /**
     * Internal helper to fetch the managed definition from the store.
     *
     * @return ServiceDefinition The blueprint being modified.
     */
    private function getDefinition() : ServiceDefinition
    {
        $definition = $this->store->get(abstract: $this->abstract);

        if ($definition === null) {
            $definition = new ServiceDefinition(abstract: $this->abstract);
            $this->store->add(definition: $definition);
        }

        return $definition;
    }

    /**
     * Associate a tag with the service for batch retrieval.
     *
     * @param string|array $tags Single tag or array of labels.
     *
     * @return self Filtered builder for fluent chaining.
     *
     */
    public function tag(string|array $tags) : self
    {
        $this->store->addTags(abstract: $this->abstract, tags: $tags);

        return $this;
    }

    /**
     * Provide a single argument override.
     *
     * @param string $name  The constructor parameter name.
     * @param mixed  $value The value or implementation to inject.
     *
     * @return self Filtered builder for fluent chaining.
     *
     */
    public function withArgument(string $name, mixed $value) : self
    {
        return $this->withArguments(arguments: [$name => $value]);
    }

    /**
     * Provide manual overrides for constructor arguments.
     *
     * @param array $arguments Map of parameter name to value/closure.
     *
     * @return self Filtered builder for fluent chaining.
     *
     */
    public function withArguments(array $arguments) : self
    {
        $definition            = $this->getDefinition();
        $definition->arguments = array_merge($definition->arguments, $arguments);

        return $this;
    }
}
