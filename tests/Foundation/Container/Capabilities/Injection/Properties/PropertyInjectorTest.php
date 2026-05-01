<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Container\Capabilities\Injection\Properties;

use Avax\Components\Application\Container\ContainerInterface;
use Avax\Components\Application\Container\DependencyInjection\Capability\Injection\Properties\PropertyInjector;
use Avax\Components\Application\Container\DependencyInjection\Capability\Prototypes\Model\PropertyPrototype;
use Avax\Components\Application\Container\DependencyInjection\Capability\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Components\Application\Container\DependencyInjection\Capability\Resolution\Errors\ResolutionException;
use Avax\Components\Application\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use Avax\Tests\TestCase;
use stdClass;

final class PropertyInjectorTest extends TestCase
{
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
            ownerClass: 'OwnerClass',
        );

        $this->assertTrue(condition: $result->resolved);
        $this->assertSame(expected: 'bar', actual: $result->value);
    }

    public function test_resolve_uses_runtime_container_for_resolvable_type() : void
    {
        $container = $this->createMock(ContainerRuntimeInterface::class);
        $container->expects(invocationRule: $this->once())
            ->method(constraint: 'resolveContext')
            ->with($this->callback(callback: static fn (KernelContext $context) : bool => $context->serviceId === stdClass::class))
            ->willReturn(value: new stdClass());

        $injector = new PropertyInjector(container: $container);
        $property = new PropertyPrototype(name: 'service', type: stdClass::class);
        $context  = new KernelContext(serviceId: 'root');

        $result = $injector->resolve(
            property  : $property,
            overrides : [],
            context   : $context,
            ownerClass: 'OwnerClass',
        );

        $this->assertTrue(condition: $result->resolved);
        $this->assertInstanceOf(expected: stdClass::class, actual: $result->value);
    }

    public function test_resolve_returns_null_when_nullable() : void
    {
        $injector = new PropertyInjector(container: $this->createMock(ContainerInterface::class));
        $property = new PropertyPrototype(name: 'nullable', type: null, allowsNull: true, required: true);
        $context  = new KernelContext(serviceId: 'root');

        $result = $injector->resolve(
            property  : $property,
            overrides : [],
            context   : $context,
            ownerClass: 'OwnerClass',
        );

        $this->assertTrue(condition: $result->resolved);
        $this->assertNull(actual: $result->value);
    }

    public function test_resolve_throws_for_required_unresolvable_property() : void
    {
        $injector = new PropertyInjector(container: $this->createMock(ContainerInterface::class));
        $property = new PropertyPrototype(name: 'required', type: null, allowsNull: false, required: true);
        $context  = new KernelContext(serviceId: 'root');

        $this->expectException(exception: ResolutionException::class);

        $injector->resolve(
            property  : $property,
            overrides : [],
            context   : $context,
            ownerClass: 'OwnerClass',
        );
    }

    public function test_resolve_returns_unresolved_when_default_exists() : void
    {
        $injector = new PropertyInjector(container: $this->createMock(ContainerInterface::class));
        $property = new PropertyPrototype(name: 'defaulted', type: null, hasDefault: true);
        $context  = new KernelContext(serviceId: 'root');

        $result = $injector->resolve(
            property  : $property,
            overrides : [],
            context   : $context,
            ownerClass: 'OwnerClass',
        );

        $this->assertFalse(condition: $result->resolved);
    }
}
