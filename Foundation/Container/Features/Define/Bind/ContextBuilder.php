<?php

declare(strict_types=1);

namespace Avax\Container\Features\Define\Bind;

use Avax\Container\Features\Core\Contracts\ContextBuilder as ContextBuilderContract;
use Avax\Container\Features\Define\Store\DefinitionStore;
use LogicException;

/**
 * Fluent API Builder for configuring contextual dependencies.
 *
 * This class facilitates the definition of specialized injection rules where a
 * specific consumer (class or wildcard pattern) receives a custom implementation
 * of a dependency, bypassing the global default.
 *
 * @package Avax\Container\Features\Define\Bind
 * @see docs/Features/Define/Bind/ContextBuilder.md
 */
class ContextBuilder implements ContextBuilderContract
{
    /** @var string The dependency identifier being targeted for override. */
    private string $needs = '';

    /**
     * Initializes a new builder for a specific consumer.
     *
     * @param DefinitionStore $store    The registry where the rule is saved.
     * @param string          $consumer The class name or pattern receiving the context.
     * @see docs/Features/Define/Bind/ContextBuilder.md#method-__construct
     */
    public function __construct(
        private readonly DefinitionStore $store,
        private readonly string          $consumer
    ) {}

    /**
     * Specify the dependency that requires a contextual override.
     *
     * @param string $abstract The dependency identifier (interface/class/alias).
     * @return self Fluid builder for the final 'give' step.
     * @see docs/Features/Define/Bind/ContextBuilder.md#method-needs
     */
    public function needs(string $abstract): self
    {
        $this->needs = $abstract;

        return $this;
    }

    /**
     * Define the implementation or value to be provided for the scoped dependency.
     *
     * @param mixed $implementation The override value, class name, or closure.
     * @return void
     * @throws LogicException If called before specifying which dependency is needed.
     *
     * @see docs/Features/Define/Bind/ContextBuilder.md#method-give
     */
    public function give(mixed $implementation): void
    {
        if ($this->needs === '') {
            throw new LogicException(
                message: "Invalid Contextual Binding: You must call 'needs()' before 'give()' to specify which dependency is being overridden."
            );
        }

        $this->store->addContextual(
            consumer: $this->consumer,
            needs: $this->needs,
            give: $implementation
        );
    }
}
