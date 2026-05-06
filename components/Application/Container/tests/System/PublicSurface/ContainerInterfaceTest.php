<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\tests\System\PublicSurface;

use Avax\Components\Application\Container\System\Container;
use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Components\Application\Container\System\Foundation\DIContainer;
use Avax\Tests\TestCase;
use DateTime;
use DateTimeImmutable;
use stdClass;

final class ContainerInterfaceTest extends TestCase
{
    private DIContainer $container;

    private Container $publicContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new DIContainer();
        $this->publicContainer = Container::fromEngine($this->container);
    }

    public function test_container_implements_interface(): void
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->publicContainer);
    }

    public function test_get_resolves_service(): void
    {
        $this->container->bind('test.service', static fn () => 'test-value');

        $result = $this->publicContainer->get('test.service');

        $this->assertSame('test-value', $result);
    }

    public function test_has_returns_true_for_bound_service(): void
    {
        $this->container->bind('bound.service', static fn () => null);

        $this->assertTrue($this->publicContainer->has('bound.service'));
    }

    public function test_has_returns_false_for_unbound(): void
    {
        $this->assertFalse($this->publicContainer->has('unbound.service'));
    }

    public function test_make_creates_new_instance(): void
    {
        $result = $this->publicContainer->make(DateTime::class);

        $this->assertInstanceOf(DateTime::class, $result);
    }

    public function test_make_with_parameters(): void
    {
        $result = $this->publicContainer->make(DateTimeImmutable::class, [
            'class' => '2024-01-01',
        ]);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
    }

    public function test_call_invokes_with_dependencies(): void
    {
        $result = $this->publicContainer->call(
            static fn (string $greeting) => $greeting,
            ['greeting' => 'Hello'],
        );

        $this->assertSame('Hello', $result);
    }

    public function test_bind_registers_service(): void
    {
        $this->publicContainer->bind('registered', static fn () => 'value');

        $this->assertTrue($this->publicContainer->has('registered'));
    }

    public function test_singleton_registers_single_instance(): void
    {
        $this->publicContainer->singleton(stdClass::class);

        $result1 = $this->publicContainer->make(stdClass::class);
        $result2 = $this->publicContainer->make(stdClass::class);

        $this->assertSame($result1, $result2);
    }

    public function test_scoped_registers_scoped_binding(): void
    {
        $this->publicContainer->scoped(stdClass::class);

        $this->assertTrue($this->publicContainer->has(stdClass::class));
    }

    public function test_instance_registers_object(): void
    {
        $object = new stdClass();
        $this->publicContainer->instance('registered.object', $object);

        $result = $this->publicContainer->get('registered.object');

        $this->assertSame($object, $result);
    }

    public function test_alias_creates_nickname(): void
    {
        $this->publicContainer->bind('original', static fn () => 'value');
        $this->publicContainer->alias('alias', 'original');

        $this->assertTrue($this->publicContainer->has('alias'));
    }

    public function test_tag_registers_tagged_services(): void
    {
        $this->publicContainer->tag(['service.a', 'service.b'], 'group');

        $this->assertTrue($this->publicContainer->has('service.a'));
        $this->assertTrue($this->publicContainer->has('service.b'));
    }

    public function test_tagged_returns_tagged_services(): void
    {
        $this->publicContainer->bind('tagged.1', static fn () => 'a');
        $this->publicContainer->bind('tagged.2', static fn () => 'b');
        $this->publicContainer->tag(['tagged.1', 'tagged.2'], 'group');

        $tagged = $this->publicContainer->tagged('group');

        $this->assertCount(2, $tagged);
    }

    public function test_flush_clears_all_bindings(): void
    {
        $this->publicContainer->bind('test', static fn () => null);
        $this->publicContainer->flush();

        $this->assertFalse($this->publicContainer->has('test'));
    }

    public function test_from_engine_creates_container(): void
    {
        $container = Container::fromEngine($this->container);

        $this->assertInstanceOf(Container::class, $container);
    }

    public function test_engine_returns_underlying_container(): void
    {
        $engine = $this->publicContainer->engine();

        $this->assertInstanceOf(ContainerInterface::class, $engine);
    }
}
