<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Inject\Contracts\PropertyInjectorInterface;
use Avax\Container\Features\Actions\Inject\InjectDependencies;
use Avax\Container\Features\Actions\Inject\Resolvers\PropertyResolution;
use Avax\Container\Features\Actions\Resolve\Contracts\DependencyResolverInterface;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\PropertyPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\Container\Features\Think\Prototype\Contracts\ServicePrototypeFactoryInterface;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Unit/InjectDependenciesTest.md#quick-summary
 */
final class InjectDependenciesTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
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

        $propertyInjector = $this->createMock(PropertyInjectorInterface::class);
        $propertyInjector->expects(invocationRule: $this->once())
            ->method(constraint: 'resolve')
            ->willReturn(value: PropertyResolution::resolved(value: 'new'));

        $resolver = $this->createMock(DependencyResolverInterface::class);

        $injector = new InjectDependencies(
            servicePrototypeFactory: $factory,
            propertyInjector       : $propertyInjector,
            resolver               : $resolver
        );

        $this->expectException(exception: ResolutionException::class);

        $injector->execute(target: $target);
    }

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @throws ReflectionException
     */
    public function test_injects_method_arguments_from_manager() : void
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

        $propertyInjector = $this->createMock(PropertyInjectorInterface::class);
        $propertyInjector->expects(invocationRule: $this->never())->method(constraint: 'resolve');

        $resolver  = $this->createMock(DependencyResolverInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $resolver->expects(invocationRule: $this->once())
            ->method(constraint: 'resolveParameters')
            ->with(
                $this->identicalTo(value: $methodPrototype->parameters),
                $this->identicalTo(value: []),
                $this->identicalTo(value: $container),
                $this->isInstanceOf(className: KernelContext::class)
            )
            ->willReturn(value: ['updated']);

        $injector = new InjectDependencies(
            servicePrototypeFactory: $factory,
            propertyInjector       : $propertyInjector,
            resolver               : $resolver,
            container              : $container
        );

        $result = $injector->execute(target: $target);

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
