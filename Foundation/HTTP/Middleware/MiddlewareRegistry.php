<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use InvalidArgumentException;
use ReflectionClass;

/**
 * PSR-15 Middleware Registry
 *
 * Provides a centralized registry of all available PSR-15 middleware
 * for DI container auto-wiring and configuration-driven middleware stacks.
 *
 * This registry enables:
 * - Easy middleware discovery and instantiation
 * - Configuration-based middleware pipeline building
 * - Type-safe middleware references
 */
final class MiddlewareRegistry
{
    /**
     * Complete map of all available PSR-15 middleware.
     *
     * Key: Short identifier for configuration
     * Value: Fully qualified class name implementing MiddlewareInterface
     */
    public const array MIDDLEWARE_MAP
        = [
            // Security middleware
            'csrf'        => CsrfVerificationMiddleware::class,
            'ip-restrict' => IpRestrictionMiddleware::class,

            // Request processing
            'cors'        => CorsMiddleware::class,
            'log'         => RequestLoggerMiddleware::class,
            'session'     => SessionLifecycleMiddleware::class,

            // Response processing
            'json'        => JsonResponseMiddleware::class,

            // Rate limiting
            'rate-limit'  => RateLimiterMiddleware::class,
        ];

    /**
     * Check if middleware identifier is registered.
     *
     * @param string $identifier The middleware identifier
     *
     * @return bool True if registered, false otherwise
     */
    public static function has(string $identifier) : bool
    {
        return isset(self::MIDDLEWARE_MAP[$identifier]);
    }

    /**
     * Get all registered middleware identifiers.
     *
     * @return string[] Array of all registered identifiers
     */
    public static function getAllIdentifiers() : array
    {
        return array_keys(self::MIDDLEWARE_MAP);
    }

    /**
     * Get middleware identifiers by category.
     *
     * @param string $category The category to filter by
     *
     * @return string[] Array of identifiers in the category
     */
    public static function getByCategory(string $category) : array
    {
        $categories = [
            'security'   => ['csrf', 'ip-restrict'],
            'processing' => ['cors', 'log', 'session'],
            'response'   => ['json'],
            'rate-limit' => ['rate-limit'],
        ];

        return $categories[$category] ?? [];
    }

    /**
     * Get middleware priority hints for pipeline ordering.
     *
     * Lower numbers = higher priority (executed first).
     * This is a suggestion - actual ordering depends on application needs.
     *
     * @return array<string, int> Priority map for middleware identifiers
     */
    public static function getPriorityHints() : array
    {
        return [
            // Execute security checks first
            'ip-restrict' => 10,
            'csrf'        => 20,

            // Then session and logging
            'session'     => 30,
            'log'         => 40,

            // CORS headers
            'cors'        => 50,

            // Rate limiting (after basic processing)
            'rate-limit'  => 60,

            // Response formatting last
            'json'        => 100,
        ];
    }

    /**
     * Create a middleware instance with dependency injection.
     *
     * This is a convenience method for simple middleware that don't need
     * complex construction. For middleware with complex dependencies,
     * use your DI container directly.
     *
     * @param string $identifier The middleware identifier
     * @param array  $args       Constructor arguments
     *
     * @return MiddlewareInterface The middleware instance
     * @throws \ReflectionException
     */
    public static function create(string $identifier, array $args = []) : MiddlewareInterface
    {
        $className = self::get(identifier: $identifier);
        self::validateMiddlewareClass(className: $className);

        $reflection = new ReflectionClass(objectOrClass: $className);
        $instance   = $reflection->newInstanceArgs(args: $args);

        return $instance;
    }

    /**
     * Get middleware class by identifier.
     *
     * @param string $identifier The middleware identifier
     *
     * @return class-string<MiddlewareInterface> The middleware class name
     *
     * @throws \InvalidArgumentException If middleware identifier is not registered
     */
    public static function get(string $identifier) : string
    {
        if (! isset(self::MIDDLEWARE_MAP[$identifier])) {
            throw new InvalidArgumentException(
                message: "Middleware '{$identifier}' is not registered. Available: " .
                implode(', ', array_keys(self::MIDDLEWARE_MAP))
            );
        }

        return self::MIDDLEWARE_MAP[$identifier];
    }

    /**
     * Validate that a class implements MiddlewareInterface.
     *
     * @param string $className The class name to validate
     *
     * @return bool True if valid middleware class
     *
     * @throws \InvalidArgumentException If class doesn't implement MiddlewareInterface
     */
    public static function validateMiddlewareClass(string $className) : bool
    {
        if (! class_exists($className)) {
            throw new InvalidArgumentException(message: "Middleware class '{$className}' does not exist.");
        }

        if (! is_subclass_of($className, MiddlewareInterface::class)) {
            throw new InvalidArgumentException(
                message: "Class '{$className}' must implement " . MiddlewareInterface::class
            );
        }

        return true;
    }
}
