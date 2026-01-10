<?php

declare(strict_types=1);
namespace Avax\Container\Features\Core\Contracts;

/**
 * Binding builder interface for fluent configuration.
 *
 * This interface defines the contract for configuring service bindings
 * with additional options like tags, arguments, and other metadata.
 *
 * @package Avax\Container\Core\Contracts
 * @see docs/Features/Core/Contracts/BindingBuilder.md#quick-summary
 */
interface BindingBuilder
{
    /**
     * Specify the concrete implementation or factory for the binding.
     *
     * @param string|callable|null $concrete
     *
     * @return self
     * @see docs/Features/Core/Contracts/BindingBuilder.md#method-to
     */
    public function to(string|callable|null $concrete) : self;

    /**
     * Add tags to the binding for grouping and filtering.
     *
     * @param string|array $tags Tag names
     *
     * @return self
     * @see docs/Features/Core/Contracts/BindingBuilder.md#method-tag
     */
    public function tag(string|array $tags) : self;

    /**
     * Set constructor arguments for the binding.
     *
     * @param array $arguments Named arguments
     *
     * @return self
     * @see docs/Features/Core/Contracts/BindingBuilder.md#method-witharguments
     */
    public function withArguments(array $arguments) : self;

    /**
     * Set a single constructor argument for the binding.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return self
     * @see docs/Features/Core/Contracts/BindingBuilder.md#method-withargument
     */
    public function withArgument(string $name, mixed $value) : self;

}
