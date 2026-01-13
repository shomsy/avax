<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Inject;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Inject\Contracts\PropertyInjectorInterface;
use Avax\Container\Features\Actions\Resolve\Contracts\DependencyResolverInterface;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\Container\Features\Think\Prototype\Contracts\ServicePrototypeFactoryInterface;
use ReflectionClass;

/**
 * Orchestrator for post-instantiation dependency injection (Setter & Property).
 *
 * While {@see Instantiator} handles constructor injection, this class handles
 * the secondary "Hydration" phase. it discovers injection points (annotated
 * properties or setter methods) and resolves them, ensuring the object is
 * fully initialized before being returned to the application.
 *
 * @see     docs/Features/Actions/Inject/InjectDependencies.md
 */
class InjectDependencies
{
    /**
     * Initializes the injection orchestrator with specialized handlers.
     *
     * @param ServicePrototypeFactoryInterface $servicePrototypeFactory    Prototype factory for injection point
     *                                                                     discovery.
     * @param PropertyInjectorInterface        $propertyInjector           Specific handler for attribute/property
     *                                                                     injection.
     * @param DependencyResolverInterface      $resolver                   Handler for method parameter resolution.
     * @param ContainerInterface|null          $container                  The container facade (for recursive lookups).
     */
    public function __construct(
        private readonly ServicePrototypeFactoryInterface $servicePrototypeFactory,
        private readonly PropertyInjectorInterface        $propertyInjector,
        private readonly DependencyResolverInterface      $resolver,
        private ContainerInterface|null                   $container = null
    ) {}

    /**
     * Wire the container reference for recursive dependency resolution.
     *
     * @param ContainerInterface $container The application container instance.
     *
     * @see docs/Features/Actions/Inject/InjectDependencies.md#method-setcontainer
     */
    public function setContainer(ContainerInterface $container) : void
    {
        $this->container = $container;
    }

    /**
     * Inject dependencies into an existing target instance (Hydration).
     *
     * @param object                    $target    The object to be hydrated.
     * @param ServicePrototype|null     $prototype Pre-analyzed injection metadata.
     * @param array<string, mixed>|null $overrides Explicit values for specific injection naming.
     * @param KernelContext|null        $context   Current resolution context for lifecycle tracking.
     *
     * @return object The hydrated object instance.
     *
     * @throws \ReflectionException
     * @see docs/Features/Actions/Inject/InjectDependencies.md#method-execute
     */
    public function execute(
        object                $target,
        ServicePrototype|null $prototype = null,
        array|null            $overrides = null,
        KernelContext|null    $context = null,
    ) : object
    {
        $overrides ??= [];
        $class     = $target::class;
        $prototype ??= $this->servicePrototypeFactory->createFor(class: $class);

        $reflection = new ReflectionClass(objectOrClass: $class);

        // 1. Property Injection (Attributes/Annotations)
        $this->injectProperties(
            target    : $target,
            prototype : $prototype,
            reflection: $reflection,
            overrides : $overrides,
            context   : $context ?? new KernelContext(serviceId: $class),
        );

        // 2. Method Injection (Setters/Hooks)
        $this->injectMethods(
            target    : $target,
            prototype : $prototype,
            reflection: $reflection,
            overrides : $overrides,
            context   : $context ?? new KernelContext(serviceId: $class),
        );

        return $target;
    }

    /**
     * Internal: Inject resolvable properties into the target object.
     *
     * @param object           $target     Target instance.
     * @param ServicePrototype $prototype  Metadata.
     * @param ReflectionClass  $reflection Reflection access.
     * @param array            $overrides  Manual values.
     * @param KernelContext    $context    Tracking context.
     *
     * @throws \ReflectionException
     */
    private function injectProperties(
        object           $target,
        ServicePrototype $prototype,
        ReflectionClass  $reflection,
        array            $overrides,
        KernelContext    $context,
    ) : void
    {
        foreach ($prototype->injectedProperties as $injectedProperty) {
            $resolution = $this->propertyInjector->resolve(
                property  : $injectedProperty,
                overrides : $overrides,
                context   : $context,
                ownerClass: $prototype->class,
            );

            if (! $resolution->resolved) {
                continue;
            }

            $property = $reflection->getProperty(name: $injectedProperty->name);

            if ($property->isReadOnly()) {
                throw new ResolutionException(
                    message: "Cannot inject readonly property \${$injectedProperty->name} in class {$prototype->class}"
                );
            }

            $property->setValue(objectOrValue: $target, value: $resolution->value);
        }
    }

    /**
     * Internal: Inject method calls into the target object by resolving parameters.
     *
     * @param object           $target     Target instance.
     * @param ServicePrototype $prototype  Metadata.
     * @param ReflectionClass  $reflection Reflection access.
     * @param array            $overrides  Manual values.
     * @param KernelContext    $context    Tracking context.
     *
     * @throws \ReflectionException
     */
    private function injectMethods(
        object           $target,
        ServicePrototype $prototype,
        ReflectionClass  $reflection,
        array            $overrides,
        KernelContext    $context,
    ) : void
    {
        if (empty($prototype->injectedMethods)) {
            return;
        }

        if ($this->container === null) {
            throw new ContainerException(message: 'Container not available for method injection.');
        }

        foreach ($prototype->injectedMethods as $methodPrototype) {
            $arguments = $this->resolver->resolveParameters(
                parameters: $methodPrototype->parameters,
                overrides : $overrides,
                container : $this->container,
                context   : $context,
            );

            $method = $reflection->getMethod(name: $methodPrototype->name);
            $method->invoke($target, ...$arguments);
        }
    }
}
