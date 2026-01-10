<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Container;
use Avax\Container\Features\Core\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Unit/ContainerBuilderTest.md#quick-summary
 */
class ContainerBuilderTest extends TestCase
{
    public function test_it_can_build_a_container_with_bindings(): void
    {
        $builder = ContainerBuilder::create();
        $builder->bind('foo', 'bar');
        $builder->singleton('baz', fn() => new stdClass());
        $container = $builder->build();

        $this->assertInstanceOf(Container::class, $container);
        $this->assertEquals('bar', $container->get('foo'));
        $this->assertInstanceOf(stdClass::class, $container->get('baz'));
        $this->assertSame($container->get('baz'), $container->get('baz'));
    }

    public function test_container_is_immutable_for_definitions(): void
    {
        $container = ContainerBuilder::create()->build();

        // Container does not have bind() method, it's enforced by type system/visibility
        $this->assertFalse(method_exists($container, 'bind'));
        $this->assertFalse(method_exists($container, 'singleton'));
        $this->assertFalse(method_exists($container, 'scoped'));
    }

    public function test_it_allows_runtime_instance_injection(): void
    {
        $container = ContainerBuilder::create()->build();
        $instance = new stdClass();

        $container->instance('current_user', $instance);

        $this->assertSame($instance, $container->get('current_user'));
    }
}
