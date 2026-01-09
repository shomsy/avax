<?php

declare(strict_types=1);
namespace Avax\Container\Features\Define\Bind;

use Avax\Container\Features\Core\Contracts\BindingBuilder as BindingBuilderContract;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\ServiceDefinition;

/**
 * @package Avax\Container\Define\Bind
 *
 * Fluent API Builder for configuring service bindings.
 *
 * The BindingBuilder provides a domain-specific language (DSL) for decorating and
 * refining a ServiceDefinition. It allows developers to specify concrete
 * implementations, add tags for batch resolution, and
 * provide manual constructor argument overrides.
 *
 * WHY IT EXISTS:
 * - To provide a readable and discoverable way to configure complex services.
 * - To abstract away the internal structure of ServiceDefinition from the end user.
 * - To ensure that modifications are consistently applied to the DefinitionStore.
 *
 * WHEN TO USE:
 * - Automatically returned by methods like bind(), singleton(), and scoped().
 * - Used during the application bootstrapping phase to wire up dependencies.
 *
 * PERFORMANCE CHARACTERISTICS:
 * - All operations are direct modifications of existing definition objects in the store.
 * - Minimal memory overhead as it only holds references to the store and abstract.
 *
 * THREAD SAFETY:
 * - Modifies shared state in the DefinitionStore; should be used during synchronous
 *   registration phase.
 *
 * @see     ServiceDefinition The underlying data structure being built.
 * @see docs_md/Features/Define/Bind/BindingBuilder.md#quick-summary
 */
readonly class BindingBuilder implements BindingBuilderContract
{
    /**
     * Initializes a new builder instance.
     *
     * @param DefinitionStore $store    The registry where the definition is persisted.
     * @param string          $abstract The identifier of the service being configured.
     * @see docs_md/Features/Define/Bind/BindingBuilder.md#method-__construct
     */
    public function __construct(
        private DefinitionStore $store,
        private string          $abstract
    ) {}

    /**
     * Specifies the concrete implementation or factory for the service.
     *
     * @param string|callable|null $concrete Class name, closure, or null (autowire same class).
     *
     * @return $this
     * @see docs_md/Features/Define/Bind/BindingBuilder.md#method-to
     */
    public function to(string|callable|null $concrete) : self
    {
        $definition = $this->store->get(abstract: $this->abstract);
        if ($definition) {
            $definition->concrete = $concrete;
        }

        return $this;
    }

    /**
     * Associates the service with one or more tags.
     *
     * Tags allow for retrieving multiple related services at once (e.g. all "middleware" tags).
     *
     * @param string|string[] $tags Single tag name or array of tag names.
     *
     * @return $this
     * @see docs_md/Features/Define/Bind/BindingBuilder.md#method-tag
     */
    public function tag(string|array $tags) : self
    {
        $this->store->addTags(abstract: $this->abstract, tags: $tags);

        return $this;
    }

    /**
     * Apply multiple named argument overrides at once.
     *
     * @param array $arguments Map of argument name to value.
     * @return $this
     * @see docs_md/Features/Define/Bind/BindingBuilder.md#method-witharguments
     */
    public function withArguments(array $arguments) : self
    {
        foreach ($arguments as $name => $value) {
            $this->withArgument(name: (string) $name, value: $value);
        }

        return $this;
    }

    /**
     * Provides an explicit override for a specific constructor parameter.
     *
     * @param string $name  The name of the parameter in the constructor.
     * @param mixed  $value The value to inject.
     *
     * @return $this
     * @see docs_md/Features/Define/Bind/BindingBuilder.md#method-withargument
     */
    public function withArgument(string $name, mixed $value) : self
    {
        $definition = $this->store->get(abstract: $this->abstract);
        if ($definition) {
            $definition->arguments[$name] = $value;
        }

        return $this;
    }
}
