<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Resolve;

use Avax\Container\Features\Actions\Instantiate\Instantiator;
use Avax\Container\Features\Actions\Resolve\Contracts\DependencyResolverInterface;
use Avax\Container\Features\Actions\Resolve\Contracts\EngineInterface;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use Avax\Container\Features\Core\Exceptions\ServiceNotFoundException;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Operate\Scope\ScopeRegistry;
use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Observe\Metrics\CollectMetrics;
use Closure;
use Throwable;

/**
 * The core resolution engine for determining and producing service instances.
 *
 * This engine acts as the "Fulfillment Orchestrator". It evaluates service requests 
 * by checking contextual rules, explicit bindings, and fallback autowiring. 
 * It manages the delegation of construction to the {@see Instantiator} while 
 * maintaining the integrity of the resolution context (parent chains, depth, loops).
 *
 * @package Avax\Container\Features\Actions\Resolve
 * @see docs/Features/Actions/Resolve/Engine.md
 */
final class Engine implements EngineInterface
{
    /** @var ContainerInternalInterface|null The container facade used for nested resolutions. */
    private ?ContainerInternalInterface $container = null;

    /**
     * Initializes the engine with essential collaborators.
     *
     * @param DependencyResolverInterface $resolver     Resolver for constructor and method parameters.
     * @param Instantiator                $instantiator The component that handles physical object creation.
     * @param DefinitionStore             $store        Central registry of service blueprints.
     * @param ScopeRegistry               $registry     Storage for shared (singleton/scoped) instances.
     * @param CollectMetrics              $metrics      Collector for performance and observability data.
     */
    public function __construct(
        private readonly DependencyResolverInterface $resolver,
        private readonly Instantiator                $instantiator,
        private readonly DefinitionStore             $store,
        private readonly ScopeRegistry               $registry,
        private readonly CollectMetrics              $metrics
    ) {}

    /**
     * Wire the container facade into the engine and its collaborators.
     *
     * @param ContainerInternalInterface $container The application container instance.
     * @see docs/Features/Actions/Resolve/Engine.md#method-setcontainer
     */
    public function setContainer(ContainerInternalInterface $container): void
    {
        $this->container = $container;
        $this->instantiator->setContainer(container: $container);
    }

    /**
     * Check if the engine has all required internal state to operate.
     *
     * @return bool True if the container reference is initialized.
     * @see docs/Features/Actions/Resolve/Engine.md#method-hasinternals
     */
    public function hasInternals(): bool
    {
        return $this->container !== null;
    }

    /**
     * Resolve a service into an instance or value based on the provided context.
     *
     * @param KernelContext $context The resolution context containing ID and overrides.
     * @return mixed The fully resolved service instance.
     * @throws ResolutionException If the service cannot be built or has missing dependencies.
     * @throws ServiceNotFoundException If the service identifier is unknown.
     *
     * @see docs/Features/Actions/Resolve/Engine.md#method-resolve
     */
    public function resolve(KernelContext $context): mixed
    {
        if ($this->container === null) {
            throw new ContainerException(message: "Container engine is not fully initialized. Call setContainer() before resolution.");
        }

        return $this->resolveFromBindings(context: $context);
    }

    /**
     * Internal logic that prioritizes bindings (Contextual > Explicit > Autowire).
     *
     * @param KernelContext $context The resolution context.
     * @return mixed The resolved value.
     */
    private function resolveFromBindings(KernelContext $context): mixed
    {
        $id = $context->serviceId;

        // 1. Check Contextual Bindings (Injected exceptions)
        if ($context->parent !== null) {
            $contextual = $this->store->getContextualMatch(consumer: $context->parent->serviceId, needs: $id);
            if ($contextual !== null) {
                return $this->evaluateConcrete(concrete: $contextual, context: $context);
            }
        }

        // 2. Check Global Definitions
        $definition = $this->store->get(abstract: $id);
        if ($definition !== null) {
            return $this->evaluateConcrete(concrete: $definition->concrete, context: $context);
        }

        // 3. Fallback: Autowiring for class strings
        if (class_exists(class: $id)) {
            return $this->instantiator->build(class: $id, overrides: $context->overrides, context: $context);
        }

        throw new ServiceNotFoundException(message: "Service [{$id}] not found in container.");
    }

    /**
     * Transform a concrete definition (Closure, Object, Class-string) into an instance.
     *
     * @param mixed         $concrete The raw concrete implementation from definition.
     * @param KernelContext $context  The current resolution context.
     * @return mixed The evaluated result.
     * @throws Throwable If closure execution or construction fails.
     */
    private function evaluateConcrete(mixed $concrete, KernelContext $context): mixed
    {

        // 1. Literal Object: Return as-is
        if (is_object(value: $concrete) && ! ($concrete instanceof Closure)) {
            return $concrete;
        }

        // 2. Closure Factory: Execute with container and parameters
        if ($concrete instanceof Closure) {
            return ($concrete)($this->container, $context->overrides);
        }

        // 3. Class String: Register Instance or Delegate
        if (is_string(value: $concrete)) {
            // Check if we are delegating to another service ($concrete !== $id)
            if ($concrete !== $context->serviceId) {
                return $this->container->resolveContext(context: $context->child(serviceId: $concrete));
            }

            // Otherwise, build the class
            return $this->instantiator->build(class: $concrete, overrides: $context->overrides, context: $context);
        }

        // 4. Literal Value (Strings/Ints/etc)
        return $concrete;
    }
}
