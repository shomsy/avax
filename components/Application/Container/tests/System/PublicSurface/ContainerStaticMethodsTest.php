<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\Tests\System\PublicSurface;

use Avax\Components\Application\Container\System\Foundation\DIContainer;
use Avax\Components\Application\Container\System\PublicSurface\Container as ContainerStatic;
use Avax\Tests\TestCase;
use DateTime;
use RuntimeException;
use stdClass;

final class ContainerStaticMethodsTest extends TestCase
{
    private DIContainer $container;

    protected function setUp() : void
    {
        parent::setUp();
        $this->container = new DIContainer;
    }

    public function test_set_container_initializes() : void
    {
        ContainerStatic::setContainer($this->container);

        $this->assertTrue(ContainerStatic::has(stdClass::class));
    }

    public function test_make_resolves_service() : void
    {
        ContainerStatic::setContainer($this->container);
        ContainerStatic::singleton(stdClass::class, new stdClass);

        $result = ContainerStatic::make(stdClass::class);

        $this->assertInstanceOf(stdClass::class, $result);
    }

    public function test_get_resolves_bound() : void
    {
        ContainerStatic::setContainer($this->container);
        ContainerStatic::bind('test.service', static fn () => new class {});

        $result = ContainerStatic::get('test.service');

        $this->assertNotNull($result);
    }

    public function test_has_checks_binding() : void
    {
        ContainerStatic::setContainer($this->container);
        ContainerStatic::bind('exists', static fn () => null);

        $this->assertTrue(ContainerStatic::has('exists'));
        $this->assertFalse(ContainerStatic::has('not.exists'));
    }

    public function test_singleton_same_instance() : void
    {
        ContainerStatic::setContainer($this->container);
        ContainerStatic::singleton(stdClass::class);

        $i1 = ContainerStatic::make(stdClass::class);
        $i2 = ContainerStatic::make(stdClass::class);

        $this->assertSame($i1, $i2);
    }

    public function test_throws_when_not_set() : void
    {
        $this->expectException(RuntimeException::class);
        ContainerStatic::get('undefined');
    }

    public function test_bound_true_when_bound() : void
    {
        ContainerStatic::setContainer($this->container);
        ContainerStatic::bind('registered', static fn () => null);

        $this->assertTrue(ContainerStatic::has('registered'));
    }

    public function test_make_creates_instance() : void
    {
        ContainerStatic::setContainer($this->container);

        $result = ContainerStatic::make(DateTime::class);

        $this->assertInstanceOf(DateTime::class, $result);
    }

    public function test_call_invokes() : void
    {
        ContainerStatic::setContainer($this->container);

        $result = ContainerStatic::call(static fn (string $foo) => $foo, ['foo' => 'bar']);

        $this->assertSame('bar', $result);
    }

    public function test_instance_registers() : void
    {
        ContainerStatic::setContainer($this->container);
        $object = new stdClass;
        ContainerStatic::instance('obj', $object);

        $this->assertTrue(ContainerStatic::has('obj'));
    }

    public function test_alias_creates_nickname() : void
    {
        ContainerStatic::setContainer($this->container);
        ContainerStatic::bind('original', static fn () => 'v');
        ContainerStatic::alias('alias', 'original');

        $this->assertTrue(ContainerStatic::has('alias'));
    }

    public function test_tag_and_tagged() : void
    {
        ContainerStatic::setContainer($this->container);
        ContainerStatic::bind('a', static fn () => 'a');
        ContainerStatic::bind('b', static fn () => 'b');
        ContainerStatic::tag(['a', 'b'], 'group');

        $this->assertCount(2, ContainerStatic::tagged('group'));
    }

    public function test_flush_clears() : void
    {
        ContainerStatic::setContainer($this->container);
        ContainerStatic::bind('test', static fn () => null);
        ContainerStatic::flush();

        $this->assertFalse(ContainerStatic::has('test'));
    }

    public function test_scoped_registers() : void
    {
        ContainerStatic::setContainer($this->container);
        ContainerStatic::scoped('scoped', static fn () => new stdClass);

        $this->assertTrue(ContainerStatic::has('scoped'));
    }
}
