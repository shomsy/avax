<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Container;

use Avax\Components\Application\Container\System\Capabilities\Bindings\BindingRegistry;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ServiceResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;

interface TestInterface {}

final class ContainerCapabilitiesTest extends TestCase
{
    private BindingRegistry $bindings;
    private ServiceResolver $resolver;

    public function test_it_resolves_class_without_dependencies() : void
    {
        $instance = $this->resolver->resolve(PlainClass::class);
        $this->assertInstanceOf(PlainClass::class, $instance);
    }

    public function test_it_resolves_class_with_dependencies() : void
    {
        $instance = $this->resolver->resolve(ClassWithDependency::class);
        $this->assertInstanceOf(ClassWithDependency::class, $instance);
        $this->assertInstanceOf(PlainClass::class, $instance->dependency);
    }

    public function test_it_resolves_singleton_bindings() : void
    {
        $this->bindings->singleton(PlainClass::class);

        $instance1 = $this->resolver->resolve(PlainClass::class);
        $instance2 = $this->resolver->resolve(PlainClass::class);

        $this->assertSame($instance1, $instance2);
    }

    public function test_it_resolves_interface_bindings() : void
    {
        $this->bindings->bind(TestInterface::class, TestImplementation::class);

        $instance = $this->resolver->resolve(TestInterface::class);
        $this->assertInstanceOf(TestImplementation::class, $instance);
    }

    public function test_it_resolves_closure_bindings() : void
    {
        $this->bindings->bind('foo', fn () => 'bar');

        $result = $this->resolver->resolve('foo');
        $this->assertSame('bar', $result);
    }

    public function test_it_throws_on_circular_dependencies() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Circular dependency');

        $this->resolver->resolve(CircularA::class);
    }

    public function test_it_calls_callable_with_dependency_injection() : void
    {
        $result = $this->resolver->call(function (PlainClass $dep) {
            return $dep;
        });

        $this->assertInstanceOf(PlainClass::class, $result);
    }

    public function test_it_injects_parameters_into_call() : void
    {
        $result = $this->resolver->call(function ($foo) {
            return $foo;
        }, ['foo' => 'bar']);

        $this->assertSame('bar', $result);
    }

    protected function setUp() : void
    {
        $this->bindings = new BindingRegistry();
        $this->resolver = new ServiceResolver($this->bindings);
    }
}

class PlainClass {}

class ClassWithDependency
{
    public function __construct(public PlainClass $dependency) {}
}

class TestImplementation implements TestInterface {}

class CircularA
{
    public function __construct(CircularB $b) {}
}

class CircularB
{
    public function __construct(CircularA $a) {}
}
