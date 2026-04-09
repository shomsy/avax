<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capability\Injection;

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Capability\Injection\InjectDependencies;
use Avax\Container\DependencyInjection\Capability\Injection\Methods\MethodInjector;
use Avax\Container\DependencyInjection\Capability\Injection\Parameters\ResolveMethodParameters;
use Avax\Container\DependencyInjection\Capability\Injection\Properties\PropertyInjector;
use Avax\Container\DependencyInjection\Capability\Prototypes\Contracts\ServicePrototypeFactoryInterface;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\MethodPrototype;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ParameterPrototype;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\PropertyPrototype;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype;
use Avax\Container\DependencyInjection\Capability\Resolution\Engine\DependencyResolver;
use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ResolutionException;
use PHPUnit\Framework\TestCase;
use ReflectionException;

final class InjectDependenciesTest extends TestCase
{
    /** @throws ReflectionException */
    public function test_injecting_readonly_property_throws() : void
    {
        $target = new ReadonlyTarget;

        $prototype = new ServicePrototype(
            class             : ReadonlyTarget::class,
            injectedProperties: [new PropertyPrototype(name: 'name', type: null)],
            injectedMethods   : []
        );

        $factory = $this->createMock(ServicePrototypeFactoryInterface::class);
        $factory->method('createFor')->willReturn(value: $prototype);

        $container = $this->createMock(ContainerInterface::class);
        $injector  = new InjectDependencies(
            servicePrototypeFactory: $factory,
            propertyInjector       : new PropertyInjector(container: $container),
            methodInjector         : new MethodInjector(
                                         parameterResolver: new ResolveMethodParameters(resolver: new DependencyResolver)
                                     ),
            container              : $container
        );

        $this->expectException(exception: ResolutionException::class);
        $injector->execute(target: $target, prototype: $prototype, overrides: ['name' => 'new']);
    }

    /** @throws ReflectionException */
    public function test_injects_method_arguments_from_overrides() : void
    {
        $target          = new MethodTarget;
        $methodPrototype = new MethodPrototype(
            name      : 'setValue',
            parameters: [new ParameterPrototype(name: 'value', type: null)]
        );
        $prototype       = new ServicePrototype(
            class             : MethodTarget::class,
            injectedProperties: [],
            injectedMethods   : [$methodPrototype]
        );

        $factory = $this->createMock(ServicePrototypeFactoryInterface::class);
        $factory->method('createFor')->willReturn(value: $prototype);

        $container = $this->createMock(ContainerInterface::class);
        $injector  = new InjectDependencies(
            servicePrototypeFactory: $factory,
            propertyInjector       : new PropertyInjector(container: $container),
            methodInjector         : new MethodInjector(
                                         parameterResolver: new ResolveMethodParameters(resolver: new DependencyResolver)
                                     ),
            container              : $container
        );

        $result = $injector->execute(target: $target, prototype: $prototype, overrides: ['value' => 'updated']);

        $this->assertSame(expected: $target, actual: $result);
        $this->assertSame(expected: 'updated', actual: $target->value);
    }
}

final readonly class ReadonlyTarget
{
    public string $name;

    public function __construct()
    {
        $this->name = 'initial';
    }
}

final class MethodTarget
{
    public string $value = '';

    public function setValue(string $value) : void
    {
        $this->value = $value;
    }
}
