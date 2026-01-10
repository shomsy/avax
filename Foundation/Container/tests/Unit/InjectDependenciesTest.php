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
    public function testInjectingReadonlyPropertyThrows() : void
    {
        $target = new ReadonlyTarget();

        $prototype = new ServicePrototype(
            class             : ReadonlyTarget::class,
            injectedProperties: [new PropertyPrototype(name: 'name', type: null)],
            injectedMethods   : []
        );

        $factory = $this->createMock(ServicePrototypeFactoryInterface::class);
        $factory->method('createFor')->willReturn($prototype);

        $propertyInjector = $this->createMock(PropertyInjectorInterface::class);
        $propertyInjector->expects($this->once())
            ->method('resolve')
            ->willReturn(PropertyResolution::resolved(value: 'new'));

        $resolver = $this->createMock(DependencyResolverInterface::class);

        $injector = new InjectDependencies(
            servicePrototypeFactory: $factory,
            propertyInjector       : $propertyInjector,
            resolver               : $resolver
        );

        $this->expectException(ResolutionException::class);

        $injector->execute(target: $target);
    }

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @throws ReflectionException
     */
    public function testInjectsMethodArgumentsFromManager() : void
    {
        $target          = new MethodTarget();
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
        $factory->method('createFor')->willReturn($prototype);

        $propertyInjector = $this->createMock(PropertyInjectorInterface::class);
        $propertyInjector->expects($this->never())->method('resolve');

        $resolver  = $this->createMock(DependencyResolverInterface::class);
        $container = $this->createMock(ContainerInterface::class);

        $resolver->expects($this->once())
            ->method('resolveParameters')
            ->with(
                $this->identicalTo($methodPrototype->parameters),
                $this->identicalTo([]),
                $this->identicalTo($container),
                $this->isInstanceOf(KernelContext::class)
            )
            ->willReturn(['updated']);

        $injector = new InjectDependencies(
            servicePrototypeFactory: $factory,
            propertyInjector       : $propertyInjector,
            resolver               : $resolver,
            container              : $container
        );

        $result = $injector->execute(target: $target);

        $this->assertSame($target, $result);
        $this->assertSame('updated', $target->value);
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
