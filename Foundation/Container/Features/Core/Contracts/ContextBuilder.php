<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Contracts;

/**
 * Context builder interface for contextual bindings.
 *
 * This interface defines the contract for configuring contextual dependencies
 * where the implementation depends on the consumer class.
 *
 * @see     docs/Features/Core/Contracts/ContextBuilder.md#quick-summary
 */
interface ContextBuilder
{
    /**
     * Specify the dependency for the consumer.
     *
     * @param string $abstract The abstract type to bind
     *
     * @see docs/Features/Core/Contracts/ContextBuilder.md#method-needs
     */
    public function needs(string $abstract) : self;

    /**
     * Specify the concrete implementation for the dependency.
     *
     * @param mixed $concrete The concrete implementation
     *
     * @see docs/Features/Core/Contracts/ContextBuilder.md#method-give
     */
    public function give(mixed $concrete) : void;
}
