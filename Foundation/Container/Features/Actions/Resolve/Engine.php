<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Resolve;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Instantiate\Instantiator;
use Avax\Container\Features\Actions\Resolve\Contracts\EngineInterface;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\ServiceDefinition;
use Avax\Container\Features\Operate\Scope\ScopeRegistry;
use Closure;

/**
 * Core resolver focusing purely on instantiation.
 *
 * @see docs_md/Features/Actions/Resolve/Engine.md#quick-summary
 */
final class Engine implements EngineInterface
{
    /**
     * @param DefinitionStore|null            $definitions   Definition store (lazy-loaded if null)
     * @param ScopeRegistry|null              $scopes        Scope registry (lazy-loaded if null)
     * @param Instantiator|null               $instantiator  Instantiator for autowiring
     * @param ContainerInternalInterface|null $container     Internal container for delegation and lazy loading
     *
     * @see docs_md/Features/Actions/Resolve/Engine.md#method-__construct
     */
    public function __construct(
        private DefinitionStore|null            $definitions = null,
        private ScopeRegistry|null              $scopes = null,
        private Instantiator|null               $instantiator = null,
        private ContainerInternalInterface|null $container = null
    ) {}

    /**
     * @see docs_md/Features/Actions/Resolve/Engine.md#method-setcontainer
     */
    public function setContainer(ContainerInternalInterface $container) : void
    {
        $this->container = $container;
        if ($this->instantiator !== null) {
            $this->instantiator->setContainer(container: $container);
        }
    }

    /**
     * @see docs_md/Features/Actions/Resolve/Engine.md#method-hasinternals
     */
    public function hasInternals() : bool
    {
        return $this->definitions !== null
            && $this->scopes !== null
            && $this->instantiator !== null;
    }

    /**
     * @see docs_md/Features/Actions/Resolve/Engine.md#method-resolve
     */
    public function resolve(KernelContext $context) : mixed
    {
        return $this->resolveFromBindings(context: $context);
    }

    /**
     * Resolve an instance/value by consulting contextual bindings, definitions, and autowiring.
     *
     * @see docs_md/Features/Actions/Resolve/Engine.md#how-it-works-technical
     */
    private function resolveFromBindings(KernelContext $context) : mixed
    {
        $abstract = $context->serviceId;

        if ($context->parent !== null) {
            $contextual = $this->getDefinitions()->getContextualMatch(
                consumer: $context->parent->serviceId,
                needs   : $abstract
            );

            if ($contextual !== null) {
                $instance = $this->resolveContextual(context: $context, contextual: $contextual);
                if (is_string($contextual)) {
                    $context->setMeta('resolution', 'delegated', true);
                }

                return $instance;
            }
        }

        $definition = $this->getDefinitions()->get(abstract: $abstract);
        if ($definition === null) {
            return $this->autowire(context: $context);
        }

        return $this->resolveDefinition(context: $context, definition: $definition);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @see docs_md/Features/Actions/Resolve/Engine.md#terminology
     */
    private function getDefinitions() : DefinitionStore
    {
        if ($this->definitions === null) {
            $this->definitions = $this->container?->get(id: DefinitionStore::class)
                ?? throw new ContainerException(message: 'DefinitionStore not available for Engine.');
        }

        return $this->definitions;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @see docs_md/Features/Actions/Resolve/Engine.md#how-it-works-technical
     */
    private function resolveContextual(KernelContext $context, mixed $contextual) : mixed
    {
        if ($contextual instanceof Closure) {
            return $contextual($this->container, $context->overrides);
        }

        if (is_string($contextual)) {
            if ($this->container instanceof ContainerInternalInterface) {
                return $this->container->resolveContext(context: $context->child(serviceId: $contextual));
            }

            return $this->container->get(id: $contextual);
        }

        return $contextual;
    }

    /**
     * Autowire a class directly when no definition exists.
     *
     * @throws ContainerException
     * @see docs_md/Features/Actions/Resolve/Engine.md#terminology
     */
    private function autowire(KernelContext $context) : mixed
    {
        if ($context->serviceId === self::class) {
            throw new ContainerException(message: 'Cannot autowire Engine recursively.');
        }

        if ($this->container === null) {
            throw new ContainerException(message: 'Engine container reference not initialized.');
        }

        if (! class_exists($context->serviceId)) {
            // If it's not a class and we're autowiring, it's a NotFound or Literal.
            // For autowire phase, we expect a class.
            throw new ContainerException(message: "Cannot autowire: class [{$context->serviceId}] not found.");
        }

        return $this->getInstantiator()->build(
            class    : $context->serviceId,
            overrides: $context->overrides,
            context  : $context
        );
    }

    /**
     * @throws ContainerException
     * @see docs_md/Features/Actions/Resolve/Engine.md#related-files--folders
     */
    private function getInstantiator() : Instantiator
    {
        if ($this->instantiator === null) {
            // Instantiator might not be bound as a service yet in some tests, but ideally engine is constructed with it.
            // If fallback needed, container->get would be ideal if we bound it.
            throw new ContainerException(message: 'Instantiator not available for Engine.');
        }

        return $this->instantiator;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @see docs_md/Features/Actions/Resolve/Engine.md#how-it-works-technical
     */
    private function resolveDefinition(KernelContext $context, ServiceDefinition $definition) : mixed
    {
        $abstract = $context->serviceId;
        $concrete = $definition->concrete ?? $abstract;

        if ($concrete instanceof Closure) {
            return $concrete($this->container, $context->overrides);
        }

        if (is_object($concrete)) {
            return $concrete;
        }

        if (is_string($concrete) && $concrete !== $abstract) {
            // Check if the concrete is a bound service or a class
            if ($this->getDefinitions()->has($concrete) || class_exists($concrete) || $this->getScopes()->has($concrete)) {
                if ($this->container instanceof ContainerInternalInterface) {
                    $instance = $this->container->resolveContext(context: $context->child(serviceId: $concrete));
                } else {
                    $instance = $this->container->get(id: $concrete);
                }
                $context->setMeta('resolution', 'delegated', true);

                return $instance;
            }

            // Allow literal string if it doesn't look like a service or class
            return $concrete;
        }

        if ($concrete === $abstract && empty($definition->arguments)) {
            return $this->autowire(context: $context);
        }

        $overrides = array_replace($definition->arguments, $context->overrides);

        if (is_string($concrete) && class_exists($concrete)) {
            return $this->getInstantiator()->build(
                class    : $concrete,
                overrides: $overrides,
                context  : $context
            );
        }

        return $concrete;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @see docs_md/Features/Actions/Resolve/Engine.md#terminology
     */
    private function getScopes() : ScopeRegistry
    {
        if ($this->scopes === null) {
            $this->scopes = $this->container?->get(id: ScopeRegistry::class)
                ?? throw new ContainerException(message: 'ScopeRegistry not available for Engine.');
        }

        return $this->scopes;
    }
}
