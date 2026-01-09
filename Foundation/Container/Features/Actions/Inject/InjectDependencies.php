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
 * Service for injecting dependencies into an existing object instance.
 *
 * @see docs_md/Features/Actions/Inject/InjectDependencies.md#quick-summary
 */
class InjectDependencies
{
    /**
     * @param ServicePrototypeFactoryInterface $servicePrototypeFactory Prototype factory for injection point discovery
     * @param PropertyInjectorInterface        $propertyInjector        Resolves property values
     * @param DependencyResolverInterface      $resolver                Resolves method parameters
     * @param ContainerInterface|null          $container               Container used for method injection
     *
     * @see docs_md/Features/Actions/Inject/InjectDependencies.md#method-__construct
     */
    public function __construct(
        private readonly ServicePrototypeFactoryInterface $servicePrototypeFactory,
        private readonly PropertyInjectorInterface        $propertyInjector,
        private readonly DependencyResolverInterface      $resolver,
        private ContainerInterface|null                   $container = null
    ) {}

    /**
     * Set the container reference used for method injection.
     *
     * @param ContainerInterface $container
     * @return void
     * @see docs_md/Features/Actions/Inject/InjectDependencies.md#method-setcontainer
     */
    public function setContainer(ContainerInterface $container) : void
    {
        $this->container = $container;
    }

    /**
     * Inject dependencies into an existing target instance.
     *
     * @param object                $target
     * @param ServicePrototype|null $prototype
     * @param array|null            $overrides
     * @param KernelContext|null    $context
     *
     * @return object
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerException
     * @throws \ReflectionException
     * @see docs_md/Features/Actions/Inject/InjectDependencies.md#method-execute
     */
    public function execute(
        object                $target,
        ServicePrototype|null $prototype = null,
        array|null            $overrides = null,
        KernelContext|null    $context = null,
    ) : object
    {
        $overrides ??= [];
        $class     = get_class($target);
        $prototype ??= $this->servicePrototypeFactory->createFor(class: $class);

        $reflection = new ReflectionClass(objectOrClass: $class);

        $this->injectProperties(
            target    : $target,
            prototype : $prototype,
            reflection: $reflection,
            overrides : $overrides,
            context   : $context ?? new KernelContext(serviceId: $class),
        );

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
     * Inject resolvable properties into the target object.
     *
     * @param object           $target
     * @param ServicePrototype $prototype
     * @param ReflectionClass  $reflection
     * @param array            $overrides
     * @param KernelContext    $context
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @see docs_md/Features/Actions/Inject/InjectDependencies.md#methods
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
            $property->setAccessible(accessible: true);

            if ($property->isReadOnly()) {
                throw new ResolutionException(
                    message: "Cannot inject readonly property \${$injectedProperty->name} in class {$prototype->class}"
                );
            }

            $property->setValue(objectOrValue: $target, value: $resolution->value);
        }
    }

    /**
     * Inject method calls into the target object by resolving parameters.
     *
     * @param object           $target
     * @param ServicePrototype $prototype
     * @param ReflectionClass  $reflection
     * @param array            $overrides
     * @param KernelContext    $context
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerException
     * @see docs_md/Features/Actions/Inject/InjectDependencies.md#methods
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
            // If we have methods to inject but no container, we can't proceed.
            // Ideally, ContainerInterface should be passed in execute() or setter injected.
            // For now, assume if it's null, we might fail or skip if no params needed?
            // Actually, DependencyResolver needs a container.
            throw new ContainerException('Container not available for method injection.');
        }

        foreach ($prototype->injectedMethods as $methodPrototype) {
            $arguments = $this->resolver->resolveParameters(
                parameters: $methodPrototype->parameters,
                overrides : $overrides,
                container : $this->container,
                context   : $context,
            );

            $method = $reflection->getMethod(name: $methodPrototype->name);
            $method->setAccessible(accessible: true);
            $method->invokeArgs(object: $target, args: $arguments);
        }
    }
}
