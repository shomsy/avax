<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Inject\PropertyInjector;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use Avax\Container\Features\Think\Model\PropertyPrototype;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Unit/PropertyInjectorTest.md#quick-summary
 */
final class PropertyInjectorTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function test_resolve_uses_overrides() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(invocationRule: $this->never())->method(constraint: 'get');

        $injector = new PropertyInjector(container: $container);
        $property = new PropertyPrototype(name: 'foo', type: 'string');
        $context  = new KernelContext(serviceId: 'root');

        $result = $injector->resolve(
            property  : $property,
            overrides : ['foo' => 'bar'],
            context   : $context,
            ownerClass: 'OwnerClass'
        );

        $this->assertTrue(condition: $result->resolved);
        $this->assertSame(expected: 'bar', actual: $result->value);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function test_resolve_uses_container_for_resolvable_type() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(invocationRule: $this->once())
            ->method(constraint: 'get')
            ->with(stdClass::class)
            ->willReturn(value: new stdClass);

        $injector = new PropertyInjector(container: $container);
        $property = new PropertyPrototype(name: 'service', type: stdClass::class);
        $context  = new KernelContext(serviceId: 'root');

        $result = $injector->resolve(
            property  : $property,
            overrides : [],
            context   : $context,
            ownerClass: 'OwnerClass'
        );

        $this->assertTrue(condition: $result->resolved);
        $this->assertInstanceOf(expected: stdClass::class, actual: $result->value);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function test_resolve_returns_null_when_nullable() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $injector  = new PropertyInjector(container: $container);
        $property  = new PropertyPrototype(name: 'nullable', type: null, allowsNull: true, required: true);
        $context   = new KernelContext(serviceId: 'root');

        $result = $injector->resolve(
            property  : $property,
            overrides : [],
            context   : $context,
            ownerClass: 'OwnerClass'
        );

        $this->assertTrue(condition: $result->resolved);
        $this->assertNull(actual: $result->value);
    }

    public function test_resolve_throws_for_required_unresolvable_property() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $injector  = new PropertyInjector(container: $container);
        $property  = new PropertyPrototype(name: 'required', type: null, allowsNull: false, required: true);
        $context   = new KernelContext(serviceId: 'root');

        $this->expectException(exception: ResolutionException::class);

        $injector->resolve(
            property  : $property,
            overrides : [],
            context   : $context,
            ownerClass: 'OwnerClass'
        );
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function test_resolve_returns_unresolved_when_default_exists() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $injector  = new PropertyInjector(container: $container);
        $property  = new PropertyPrototype(name: 'defaulted', type: null, hasDefault: true);
        $context   = new KernelContext(serviceId: 'root');

        $result = $injector->resolve(
            property  : $property,
            overrides : [],
            context   : $context,
            ownerClass: 'OwnerClass'
        );

        $this->assertFalse(condition: $result->resolved);
    }
}
