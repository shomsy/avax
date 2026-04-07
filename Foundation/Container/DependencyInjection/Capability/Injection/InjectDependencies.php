<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Injection;

use Avax\Container\DependencyInjection\Capability\Injection\Methods\MethodInjector;
use Avax\Container\DependencyInjection\Capability\Injection\Properties\PropertyInjector;
use Avax\Container\DependencyInjection\Capability\Prototypes\Contracts\ServicePrototypeFactoryInterface;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype;
use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ContainerException;
use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ResolutionException;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * Orchestrates post-instantiation property and method injection.
 */
final class InjectDependencies
{
    public function __construct(
        private readonly ServicePrototypeFactoryInterface $servicePrototypeFactory,
        private readonly PropertyInjector                 $propertyInjector,
        private readonly MethodInjector                   $methodInjector,
        private ContainerInterface|null                   $container = null
    ) {}

    public function setContainer(ContainerInterface $container) : void
    {
        $this->container = $container;
    }

    public function execute(
        object $target,
        ServicePrototype|null $prototype = null,
        array|null $overrides = null,
        KernelContext|null $context = null
    ) : object
    {
        $overrides ??= [];
        $class     = $target::class;
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

    private function injectProperties(
        object $target,
        ServicePrototype $prototype,
        ReflectionClass $reflection,
        array $overrides,
        KernelContext $context
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

    private function injectMethods(
        object $target,
        ServicePrototype $prototype,
        ReflectionClass $reflection,
        array $overrides,
        KernelContext $context
    ) : void
    {
        if (empty($prototype->injectedMethods)) {
            return;
        }

        if ($this->container === null) {
            throw new ContainerException(message: 'Container not available for method injection.');
        }

        $this->methodInjector->inject(
            target    : $target,
            prototype : $prototype,
            reflection: $reflection,
            overrides : $overrides,
            container : $this->container,
            context   : $context
        );
    }
}
