<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\ComponentRegistry;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentDefinition;
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
        $componentRegistry = new ComponentRegistry();

        self::assertFalse($componentRegistry->has(name: 'any'));
        self::assertSame([], $componentRegistry->all());
        self::assertSame([], $componentRegistry->names());
    }

    #[Test]
    public function it_registers_component_definition(): void
    {
        $componentRegistry = new ComponentRegistry();

        $componentRegistry->register(
            componentDefinition: new ComponentDefinition(
                name         : 'cache',
                providerClass: 'CacheProvider',
            ),
        );

        self::assertTrue($componentRegistry->has(name: 'cache'));
        self::assertCount(1, $componentRegistry->all());
        self::assertSame(['cache'], $componentRegistry->names());
    }

    #[Test]
    public function it_throws_when_registering_duplicate_component(): void
    {
        $componentRegistry = new ComponentRegistry();

        $componentRegistry->register(
            componentDefinition: new ComponentDefinition(name: 'cache'),
        );

        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Component "cache" is already registered.');

        $componentRegistry->register(
            componentDefinition: new ComponentDefinition(name: 'cache'),
        );
    }

    #[Test]
    public function it_stores_provider_class_in_definition(): void
    {
        $componentRegistry = new ComponentRegistry();

        $componentRegistry->register(
            componentDefinition: new ComponentDefinition(
                name         : 'events',
                providerClass: 'EventsProvider',
            ),
        );

        $definitions = $componentRegistry->all();
        self::assertSame('EventsProvider', $definitions[0]->providerClass());
    }

    #[Test]
    public function it_can_register_and_boot_provider(): void
    {
        $componentRegistry = new ComponentRegistry();

        $componentProvider = new class () implements ComponentProviderInterface {
            public static function name(): string
            {
                return 'test';
            }

            public function boot(RuntimeInterface $runtime): void
            {
            }
        };

        $componentRegistry->registerProvider(componentProvider: $componentProvider);

        self::assertTrue($componentRegistry->has(name: 'test'));
    }

    #[Test]
    public function it_boots_all_registered_providers(): void
    {
        $componentRegistry = new ComponentRegistry();
        $booted   = [];

        $componentProvider = new class ($booted) implements ComponentProviderInterface {
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

        $componentRegistry->registerProvider(componentProvider: $componentProvider);

        $runtime = $this->createMock(RuntimeInterface::class);
        $componentRegistry->boot(runtime: $runtime);

        self::assertCount(1, $booted);
    }

    #[Test]
    public function it_returns_component_definition(): void
    {
        $componentRegistry = new ComponentRegistry();

        $componentRegistry->register(
            componentDefinition: new ComponentDefinition(
                name         : 'router',
                providerClass: 'RouterProvider',
            ),
        );

        $all = $componentRegistry->all();

        self::assertCount(1, $all);
        self::assertInstanceOf(ComponentDefinition::class, $all[0]);
    }
}
