<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\PublicSurface;

use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use RuntimeException;

/**
 * Container - Laravel-style static access to the DI container.
 *
 * This is the main entry point for the Container component.
 * Use it globally across the system.
 *
 * Usage:
 *   Container::setContainer($container);
 *   Container::make(Service::class);
 *   Container::get('service');
 *   Container::has('service');
 */
class Container implements ResettableState
{
    private static ?ContainerInterface $container = null;

    private ContainerInterface|null $engine = null;

    public function resetState(): void
    {
        self::$container = null;
        $this->engine = null;
    }

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * Set a container instance (alias).
     */
    public static function initialize(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * Create container facade from underlying engine.
     */
    public static function fromEngine(ContainerInterface $container): self
    {
        $instance = new self();
        $instance->engine = $container;

        self::$container = $container;

        return $instance;
    }

    /**
     * Get the underlying container engine.
     */
    public function engine(): ContainerInterface
    {
        return $this->engine ?? self::$container;
    }

    public static function make(string $abstract, array $parameters = []): object
    {
        return self::getContainer()->make($abstract, $parameters);
    }

    public static function has(string $id): bool
    {
        return self::getContainer()->has($id);
    }

    public static function get(string $id): mixed
    {
        return self::getContainer()->get($id);
    }

    public static function call(callable|string $callable, array $parameters = []): mixed
    {
        return self::getContainer()->call($callable, $parameters);
    }

    public static function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
    {
        self::getContainer()->bind($abstract, $concrete, $shared);
    }

    public static function singleton(string $abstract, mixed $concrete = null): void
    {
        self::getContainer()->singleton($abstract, $concrete);
    }

    public static function scoped(string $abstract, mixed $concrete = null): void
    {
        self::getContainer()->scoped($abstract, $concrete);
    }

    public static function instance(string $abstract, object $instance): void
    {
        self::getContainer()->instance($abstract, $instance);
    }

    public static function alias(string $alias, string $abstract): void
    {
        self::getContainer()->alias($alias, $abstract);
    }

    public static function tag(string|array $abstracts, string|array $tags): void
    {
        self::getContainer()->tag($abstracts, $tags);
    }

    public static function tagged(string $tag): array
    {
        return self::getContainer()->tagged($tag);
    }

    public static function flush(): void
    {
        self::getContainer()->flush();
    }

    private static function getContainer(): ContainerInterface
    {
        if (! self::$container instanceof ContainerInterface) {
            throw new RuntimeException('Container not set. Call Container::setContainer() first.');
        }

        return self::$container;
    }
}
