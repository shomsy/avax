<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline;

use InvalidArgumentException;

/**
 * Registry for middleware aliases, groups, and factory creation.
 */
final class MiddlewareRegistry
{
    private static array $aliases = [];

    private static array $factories = [];

    /**
     * Register a middleware alias with a factory.
     */
    public static function register(string $alias, callable $factory): void
    {
        self::$aliases[$alias] = $factory;
    }

    /**
     * Check if a middleware alias exists.
     */
    public static function has(string $alias): bool
    {
        return isset(self::$aliases[$alias]);
    }

    /**
     * Create a middleware instance from an alias.
     *
     * @throws InvalidArgumentException When middleware alias is not registered
     */
    public static function create(string $alias, array $args = []): MiddlewareInterface
    {
        if (! isset(self::$aliases[$alias])) {
            throw new InvalidArgumentException(sprintf('Middleware alias [%s] is not registered.', $alias));
        }

        return (self::$aliases[$alias])(...$args);
    }

    /**
     * Get priority hints for middleware ordering.
     */
    public static function getPriorityHints(): array
    {
        return array_keys(self::$aliases);
    }

    /**
     * Clear all registered middleware.
     */
    public static function clear(): void
    {
        self::$aliases = [];
        self::$factories = [];
    }

    /**
     * Reset static state for long-lived worker safety.
     */
    public static function reset() : void
    {
        self::$aliases   = [];
        self::$factories = [];
    }
}
