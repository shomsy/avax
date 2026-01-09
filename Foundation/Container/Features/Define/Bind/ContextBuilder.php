<?php

declare(strict_types=1);
namespace Avax\Container\Features\Define\Bind;

use Avax\Container\Features\Core\Contracts\ContextBuilder as ContextBuilderContract;
use Avax\Container\Features\Define\Store\DefinitionStore;
use LogicException;

/**
 * @package Avax\Container\Define\Bind
 *
 * Fluent API Builder for configuring contextual dependencies.
 *
 * The ContextBuilder allows for fine-grained control over dependency injection by
 * specifying that a particular class (consumer) should receive a specific
 * implementation for one of its dependencies, overriding the global binding.
 *
 * WHY IT EXISTS:
 * - To solve the "Ambiguous Implementation" problem where multiple classes implement
 *   the same interface but different consumers need different implementations.
 * - To allow for specialized configuration of services based on where they are injected.
 * - To provide a safe and readable way to manage complex object graphs.
 *
 * USAGE EXAMPLE:
 * ```php
 * $container->when(ReportService::class)
 *           ->needs(LoggerInterface::class)
 *           ->give(FileLogger::class);
 * ```
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Contextual rules are stored in a specialized map within the DefinitionStore.
 * - Fast path lookups use string concatenation as keys.
 *
 * THREAD SAFETY:
 * - Modifies shared state in the DefinitionStore; should be used during synchronous
 *   registration phase.
 *
 * @see docs_md/Features/Define/Bind/ContextBuilder.md#quick-summary
 */
class ContextBuilder implements ContextBuilderContract
{
    /** @var string The temporary holder for the dependency identifier during the two-step build. */
    private string $needs = '';

    /**
     * Initializes a new builder for a specific consumer.
     *
     * @param DefinitionStore $store    The destination registry for the contextual rule.
     * @param string          $consumer The class name (or pattern) that receives the dependency.
     * @see docs_md/Features/Define/Bind/ContextBuilder.md#method-__construct
     */
    public function __construct(
        private readonly DefinitionStore $store,
        private readonly string          $consumer
    ) {}

    /**
     * Step 1: Define which dependency identifier is being specialized.
     *
     * @param string $abstract The interface or class name required by the consumer.
     *
     * @return $this
     * @see docs_md/Features/Define/Bind/ContextBuilder.md#method-needs
     */
    public function needs(string $abstract) : self
    {
        $this->needs = $abstract;

        return $this;
    }

    /**
     * Step 2: Define the implementation to be provided for the previously defined need.
     *
     * @param mixed $implementation A class name, factory closure, or specific object instance.
     *
     * @throws LogicException If called before needs().
     * @see docs_md/Features/Define/Bind/ContextBuilder.md#method-give
     */
    public function give(mixed $implementation) : void
    {
        if ($this->needs === '') {
            throw new LogicException(message: "Cannot call give() without defining what is needed first via needs().");
        }

        $this->store->addContextual(consumer: $this->consumer, needs: $this->needs, give: $implementation);

        // Reset state to ensure atomic operations
        $this->needs = '';
    }
}
