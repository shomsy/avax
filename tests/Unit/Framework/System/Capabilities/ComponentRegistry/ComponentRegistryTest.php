<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\ComponentRegistry;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentDefinition;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentNotRegistered;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Avax\Tests\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ComponentRegistry::class)]
#[CoversClass(ComponentDefinition::class)]
final class ComponentRegistryTest extends TestCase
{
    #[Test]
    public function it_is_empty_when_created(): void
    {
        $registry = new ComponentRegistry();

        self::assertFalse($registry->has(name: 'any'));
        self::assertSame([], $registry->all());
        self::assertSame([], $registry->names());
    }

    #[Test]
    public function it_registers_component_definition(): void
    {
        $registry = new ComponentRegistry();

        $registry->register(
            definition: new ComponentDefinition(
                name         : 'cache',
                providerClass: 'CacheProvider',
            ),
        );

        self::assertTrue($registry->has(name: 'cache'));
        self::assertCount(1, $registry->all());
        self::assertSame(['cache'], $registry->names());
    }

    #[Test]
    public function it_throws_when_registering_duplicate_component(): void
    {
        $registry = new ComponentRegistry();

        $registry->register(
            definition: new ComponentDefinition(name: 'cache'),
        );

        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Component "cache" is already registered.');

        $registry->register(
            definition: new ComponentDefinition(name: 'cache'),
        );
    }

    #[Test]
    public function it_stores_provider_class_in_definition(): void
    {
        $registry = new ComponentRegistry();

        $registry->register(
            definition: new ComponentDefinition(
                name         : 'events',
                providerClass: 'EventsProvider',
            ),
        );

        $definitions = $registry->all();
        self::assertSame('EventsProvider', $definitions[0]->providerClass());
    }

    #[Test]
    public function it_can_register_and_boot_provider(): void
    {
        $registry = new ComponentRegistry();

        $provider = new class implements ComponentProviderInterface {
            public static function name(): string
            {
                return 'test';
            }

            public function boot(RuntimeInterface $runtime): void
            {
            }
        };

        $registry->registerProvider(provider: $provider);

        self::assertTrue($registry->has(name: 'test'));
    }

    #[Test]
    public function it_boots_all_registered_providers(): void
    {
        $registry = new ComponentRegistry();
        $booted = [];

        $provider = new class($booted) implements ComponentProviderInterface {
            public function __construct(private array &$booted)
            {
            }

            public static function name(): string
            {
                return 'booted';
            }

            public function boot(RuntimeInterface $runtime): void
            {
                $this->booted[] = 'booted';
            }
        };

        $registry->registerProvider(provider: $provider);

        $runtime = $this->createMock(RuntimeInterface::class);
        $registry->boot(runtime: $runtime);

        self::assertCount(1, $booted);
    }

    #[Test]
    public function it_returns_component_definition(): void
    {
        $registry = new ComponentRegistry();

        $registry->register(
            definition: new ComponentDefinition(
                name         : 'router',
                providerClass: 'RouterProvider',
            ),
        );

        $all = $registry->all();

        self::assertCount(1, $all);
        self::assertInstanceOf(ComponentDefinition::class, $all[0]);
    }
}