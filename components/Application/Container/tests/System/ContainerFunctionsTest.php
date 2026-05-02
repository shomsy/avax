<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\Tests\System;

use Avax\Components\Application\Container\System\Foundation\DIContainer;
use Avax\Tests\TestCase;
use DateTime;
use DateTimeImmutable;
use stdClass;

final class ContainerFunctionsTest extends TestCase
{
    private DIContainer $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new DIContainer();
        appInstance($this->container);
    }

    protected function tearDown(): void
    {
        // Clear the app instance
        appInstance(null);

        parent::tearDown();
    }

    public function test_app_instance_gets_container(): void
    {
        $result = appInstance();

        $this->assertSame($this->container, $result);
    }

    public function test_app_instance_sets_container(): void
    {
        $newContainer = new DIContainer();

        appInstance($newContainer);

        $this->assertSame($newContainer, appInstance());
    }

    public function test_app_resolves_service(): void
    {
        $this->container->bind('test.service', static fn () => 'test-value');

        $result = app('test.service');

        $this->assertSame('test-value', $result);
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
        $instance = new class () {
            public string $value;
        };

        $result = make($instance::class, ['value' => 'test']);

        $this->assertSame('test', $result->value);
    }

    public function test_bind_registers_binding(): void
    {
        bind('bound.service', static fn () => 'value');

        $result = $this->container->get('bound.service');

        $this->assertSame('value', $result);
    }

    public function test_bind_with_shared_parameter(): void
    {
        bind('shared.service', static fn () => new stdClass(), shared: true);

        $result1 = $this->container->make('shared.service');
        $result2 = $this->container->make('shared.service');

        $this->assertSame($result1, $result2);
    }

    public function test_singleton_registers_single_instance(): void
    {
        singleton('single', static fn () => new stdClass());

        $result1 = $this->container->make('single');
        $result2 = $this->container->make('single');

        $this->assertSame($result1, $result2);
    }

    public function test_resolve_alias_for_make(): void
    {
        $result = resolve(DateTimeImmutable::class);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
    }

    public function test_resolve_with_parameters(): void
    {
        $instance = new class ('param') {
            public function __construct(public string $param)
            {
            }
        };

        $result = resolve($instance::class, ['param' => 'value']);

        $this->assertSame('value', $result->param);
    }
}
