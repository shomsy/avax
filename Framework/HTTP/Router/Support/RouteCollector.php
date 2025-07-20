<?php

declare(strict_types=1);

namespace Gemini\HTTP\Router\Support;

use Gemini\HTTP\Router\Routing\RouteBuilder;
use LogicException;

/**
 * Class RouteCollector
 *
 * Provides a temporary in-memory registry for storing route configurations (via `RouteBuilder` instances)
 * during application initialization phases, such as bootstrapping, cache compilation,
 * or CLI-based route inspection.
 *
 * This class is designed for temporary usage and does not handle runtime route resolution.
 * It serves only as an internal tool for assembling and managing router-related data.
 */
final class RouteCollector
{
    /**
     * @var list<RouteBuilder> $bufferedRoutes
     *
     * Buffers all the route definitions provided during application initialization.
     * This buffer is emptied after flushing or resetting, maintaining the ephemeral nature of this class.
     */
    private static array $bufferedRoutes = [];

    /**
     * @var callable|array|string|null $fallback
     *
     * Defines the fallback handler for unmatched routes.
     * This handler is called at runtime when no route matches are found.
     * Accepts `callable`, an array (controller-action pair), or a string (class or function name).
     */
    private static mixed $fallback = null;

    /**
     * Registers a RouteBuilder into the internal buffered routes registry.
     *
     * RouteBuilder instances are used to encapsulate route definitions and related metadata.
     *
     * @param RouteBuilder $builder The RouteBuilder instance to buffer.
     *
     * @return void
     */
    public static function add(RouteBuilder $builder) : void
    {
        // Add the provided RouteBuilder instance to the buffered routes list.
        self::$bufferedRoutes[] = $builder;
    }

    /**
     * Returns all buffered RouteBuilder instances and clears the buffer.
     *
     * This method is essential during cache compilation or inspection tasks,
     * where it retrieves and empties the stored entries for processing downstream.
     *
     * @return list<RouteBuilder> A list of buffered RouteBuilder instances.
     */
    public static function flushBuffered() : array
    {
        // Assign the current buffer to a temporary variable for returning.
        $routes = self::$bufferedRoutes;

        // Clear the buffered routes to ensure the collector is reset post-flush.
        self::$bufferedRoutes = [];

        // Return the temporary stash of routes.
        return $routes;
    }

    /**
     * Defines a fallback handler for unmatched routes.
     *
     * This operation is important and enforces a single fallback definition.
     * Calling this method multiple times will result in an exception if the fallback is already defined.
     *
     * @param callable|array|string $handler A handler for unmatched routes. This can be:
     *                                       - A `callable` (e.g., closure, function),
     *                                       - A controller-action pair array (e.g., [Controller::class, 'method']),
     *                                       - A string (e.g., fully qualified class name or function).
     *
     * @return void
     * @throws LogicException If a fallback handler has already been set.
     *
     */
    public static function fallback(callable|array|string $handler) : void
    {
        // Prevent overriding an existing fallback handler by throwing an exception.
        if (self::$fallback !== null) {
            throw new LogicException(message: 'Fallback route handler has already been defined.');
        }

        // Set the fallback handler.
        self::$fallback = $handler;
    }

    /**
     * Retrieves the currently set fallback handler.
     *
     * This method is designed to allow downstream consumers to inspect the state
     * of the collector for unmatched route handling.
     *
     * @return callable|array|string|null The fallback handler, or null if none is set.
     */
    public static function getFallback() : callable|array|string|null
    {
        // Return the current fallback handler.
        return self::$fallback;
    }

    /**
     * Clears the currently set fallback handler.
     *
     * This method ensures a clean state, consistent with the stateless purpose of the collector.
     *
     * @return void
     */
    public static function clearFallback() : void
    {
        // Reset the fallback handler to null.
        self::$fallback = null;
    }

    /**
     * Checks whether the collector contains any buffered RouteBuilder instances.
     *
     * This method helps optimize workflows or conditional operations during bootstrap or cache validation.
     *
     * @return bool True if there are buffered routes, false otherwise.
     */
    public static function hasRoutes() : bool
    {
        // Return true if the bufferedRoutes array is not empty.
        return ! empty(self::$bufferedRoutes);
    }

    /**
     * Resets the entire collector to a clean state.
     *
     * This method clears all buffered routes and removes the fallback handler, ensuring no side effects or
     * lingering state between bootstrap cycles or application contexts.
     *
     * @return void
     */
    public static function reset() : void
    {
        // Clear the buffered routes.
        self::$bufferedRoutes = [];
        // Reset the fallback handler to null.
        self::$fallback = null;
    }
}