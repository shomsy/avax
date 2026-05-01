<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\PublicSurface;

use Avax\Components\Application\Container\System\Foundation\DIContainerInterface;
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
class Container
{
    private static ?DIContainerInterface $container = null;

    public static function setContainer(DIContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * Set a container instance (alias).
     */
    public static function initialize(DIContainerInterface $container): void
    {
        self::$container = $container;
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

    private static function getContainer(): DIContainerInterface
    {
        if (self::$container === null) {
            throw new RuntimeException('Container not set. Call Container::setContainer() first.');
        }

        return self::$container;
    }
}
