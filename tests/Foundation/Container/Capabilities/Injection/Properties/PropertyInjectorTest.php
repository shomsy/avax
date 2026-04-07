<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capabilities\Injection\Properties;

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Capabilities\Injection\Properties\PropertyInjector;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Model\PropertyPrototype;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Errors\ResolutionException;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel\KernelContext;
use PHPUnit\Framework\TestCase;
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
            ownerClass: 'OwnerClass'
        );

        $this->assertTrue(condition: $result->resolved);
        $this->assertSame(expected: 'bar', actual: $result->value);
    }

    public function test_resolve_uses_runtime_container_for_resolvable_type() : void
    {
        $container = $this->createMock(ContainerRuntimeInterface::class);
        $container->expects(invocationRule: $this->once())
            ->method(constraint: 'resolveContext')
            ->with($this->callback(static function (KernelContext $context) : bool {
                return $context->serviceId === stdClass::class;
            }))
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

    public function test_resolve_returns_null_when_nullable() : void
    {
        $injector = new PropertyInjector(container: $this->createMock(ContainerInterface::class));
        $property = new PropertyPrototype(name: 'nullable', type: null, allowsNull: true, required: true);
        $context  = new KernelContext(serviceId: 'root');

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
        $injector = new PropertyInjector(container: $this->createMock(ContainerInterface::class));
        $property = new PropertyPrototype(name: 'required', type: null, allowsNull: false, required: true);
        $context  = new KernelContext(serviceId: 'root');

        $this->expectException(exception: ResolutionException::class);

        $injector->resolve(
            property  : $property,
            overrides : [],
            context   : $context,
            ownerClass: 'OwnerClass'
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
            ownerClass: 'OwnerClass'
        );

        $this->assertFalse(condition: $result->resolved);
    }
}
