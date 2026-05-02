<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Container\System\PublicSurface;

use Avax\Components\Application\Container\System\Foundation\DIContainer;
use Avax\Tests\TestCase;
use DateTime;
use DateTimeImmutable;
use stdClass;

final class ContainerFunctionsTest extends TestCase
{
    private DIContainer $container;

    public function test_app_instance_returns_container(): void
    {
        $this->assertSame($this->container, appInstance());
    }

    public function test_app_instance_sets_container(): void
    {
        $new = new DIContainer();
        appInstance($new);
        $this->assertSame($new, appInstance());
    }

    public function test_app_resolves_service(): void
    {
        $this->container->bind('test', static fn () => 'value');

        $result = app('test');

        $this->assertSame('value', $result);
    }

    public function test_app_returns_container_when_null(): void
    {
        $result = app();
        $this->assertSame($this->container, $result);
    }

    public function test_make_creates_instance(): void
    {
        $result = make(DateTime::class);
        $this->assertInstanceOf(DateTime::class, $result);
    }

    public function test_make_with_parameters(): void
    {
        $instance = new class ('test') {
            public function __construct(public string $v)
            {
            }
        };

        $result = make($instance::class, ['v' => 'hello']);

        $this->assertSame('hello', $result->v);
    }

    public function test_bind_registers(): void
    {
        bind('test', static fn () => 'value');

        $this->assertTrue($this->container->has('test'));
    }

    public function test_singleton_same_instance(): void
    {
        singleton('single', static fn () => new stdClass());

        $i1 = $this->container->make('single');
        $i2 = $this->container->make('single');

        $this->assertSame($i1, $i2);
    }

    public function test_resolve_alias_for_make(): void
    {
        $result = resolve(DateTimeImmutable::class);
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
    }

    public function test_register_alias_for_bind(): void
    {
        register('test', static fn () => 'value');

        $this->assertTrue($this->container->has('test'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new DIContainer();
        appInstance($this->container);
    }

    protected function tearDown(): void
    {
        appInstance(null);
        parent::tearDown();
    }
}
