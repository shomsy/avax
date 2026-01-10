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
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function testResolveUsesOverrides(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())->method('get');

        $injector = new PropertyInjector(container: $container);
        $property = new PropertyPrototype(name: 'foo', type: 'string');
        $context  = new KernelContext(serviceId: 'root');

        $result = $injector->resolve(
            property: $property,
            overrides: ['foo' => 'bar'],
            context: $context,
            ownerClass: 'OwnerClass'
        );

        $this->assertTrue($result->resolved);
        $this->assertSame('bar', $result->value);
    }

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function testResolveUsesContainerForResolvableType(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with(stdClass::class)
            ->willReturn(new stdClass());

        $injector = new PropertyInjector(container: $container);
        $property = new PropertyPrototype(name: 'service', type: stdClass::class);
        $context  = new KernelContext(serviceId: 'root');

        $result = $injector->resolve(
            property: $property,
            overrides: [],
            context: $context,
            ownerClass: 'OwnerClass'
        );

        $this->assertTrue($result->resolved);
        $this->assertInstanceOf(stdClass::class, $result->value);
    }

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function testResolveReturnsNullWhenNullable(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $injector = new PropertyInjector(container: $container);
        $property = new PropertyPrototype(name: 'nullable', type: null, allowsNull: true, required: true);
        $context  = new KernelContext(serviceId: 'root');

        $result = $injector->resolve(
            property: $property,
            overrides: [],
            context: $context,
            ownerClass: 'OwnerClass'
        );

        $this->assertTrue($result->resolved);
        $this->assertNull($result->value);
    }

    public function testResolveThrowsForRequiredUnresolvableProperty(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $injector = new PropertyInjector(container: $container);
        $property = new PropertyPrototype(name: 'required', type: null, allowsNull: false, required: true);
        $context  = new KernelContext(serviceId: 'root');

        $this->expectException(ResolutionException::class);

        $injector->resolve(
            property: $property,
            overrides: [],
            context: $context,
            ownerClass: 'OwnerClass'
        );
    }

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function testResolveReturnsUnresolvedWhenDefaultExists(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $injector = new PropertyInjector(container: $container);
        $property = new PropertyPrototype(name: 'defaulted', type: null, hasDefault: true);
        $context  = new KernelContext(serviceId: 'root');

        $result = $injector->resolve(
            property: $property,
            overrides: [],
            context: $context,
            ownerClass: 'OwnerClass'
        );

        $this->assertFalse($result->resolved);
    }
}
