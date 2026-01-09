<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Container;
use Avax\Container\Features\Core\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class ContainerBuilderTest extends TestCase
{
    public function test_it_can_build_a_container_with_bindings(): void
    {
        $builder = ContainerBuilder::create();
        $builder->bind('foo', 'bar');
        $builder->singleton('baz', fn() => new \stdClass());
        $container = $builder->build();

        $this->assertInstanceOf(Container::class, $container);
        $this->assertEquals('bar', $container->get('foo'));
        $this->assertInstanceOf(\stdClass::class, $container->get('baz'));
        $this->assertSame($container->get('baz'), $container->get('baz'));
    }

    public function test_registration_methods_throw_exception_at_runtime(): void
    {
        $container = ContainerBuilder::create()->build();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('immutable');

        $container->bind('foo', 'bar');
    }

    public function test_it_allows_runtime_instance_injection(): void
    {
        $container = ContainerBuilder::create()->build();
        $instance = new \stdClass();

        $container->instance('current_user', $instance);

        $this->assertSame($instance, $container->get('current_user'));
    }

    public function test_analyzer_is_accessible_on_builder(): void
    {
        $builder = ContainerBuilder::create();
        $builder->bind(\stdClass::class, \stdClass::class);

        $analyzer = $builder->analyze();
        $this->assertInstanceOf(\Avax\Container\Features\Think\Analyzer::class, $analyzer);
        $analyzer->warmUp();

        $container = $builder->build();
        $this->assertTrue($container->has(\stdClass::class));
    }
}
