<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\Foundation;

use Override;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Base Facade with __callStatic proxy to container.
 */
abstract class Facade implements FacadeInterface
{
    protected static string $accessor = '';

    /** @var array<string, mixed> Cached resolved instances */
    protected static array $resolvedInstances = [];

    protected static ?ContainerInterface $container = null;

    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = static::resolveInstance();

        if ($instance === null) {
            throw new RuntimeException("Facade accessor '".static::getFacadeAccessor()."' could not be resolved.");
        }

        return $instance->{$method}(...$args);
    }

    #[Override]
    public static function getFacadeAccessor(): string
    {
        return static::$accessor;
    }

    #[Override]
    public static function clearResolvedInstance(): void
    {
        unset(static::$resolvedInstances[static::getFacadeAccessor()]);
    }

    public static function clearAllResolvedInstances(): void
    {
        static::$resolvedInstances = [];
    }

    public static function setContainer(ContainerInterface $container): void
    {
        static::$container = $container;
    }

    /**
     * Replace the facade's resolved instance with a fake.
     */
    public static function fake(callable|object|null $callback = null): mixed
    {
        if ($callback === null) {
            $callback = static fn (): null => null;
        }

        $instance = is_callable($callback) && ! is_object($callback) ? $callback() : $callback;
        static::$resolvedInstances[static::getFacadeAccessor()] = $instance;

        return $instance;
    }

    protected static function resolveInstance(): mixed
    {
        $accessor = static::getFacadeAccessor();

        // Return cached instance if available
        if (isset(static::$resolvedInstances[$accessor])) {
            return static::$resolvedInstances[$accessor];
        }

        // Resolve from container
        if (static::$container instanceof ContainerInterface) {
            return static::$resolvedInstances[$accessor] = static::$container->get($accessor);
        }

        // Fallback to global app() function
        if (function_exists('app')) {
            $instance = app($accessor);
            if ($instance !== null) {
                return static::$resolvedInstances[$accessor] = $instance;
            }
        }

        throw new RuntimeException(sprintf("No container available to resolve facade accessor '%s'", $accessor));
    }
}
