<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * Interface for route loading strategies.
 *
 * Defines the contract for loading routes from different sources (cache, disk, etc.)
 * enabling unified route loading architecture.
 */
interface RouteSourceLoaderInterface
{
    /**
     * Load routes into the provided collection.
     *
     * Implementations should populate the RouteCollection with routes from their source,
     * handling any necessary validation, caching, or error handling.
     *
     * @param RouteCollection $collection The collection to populate with routes
     *
     * @throws \Exception If loading fails for any reason
     */
    public function loadInto(RouteCollection $collection) : void;

    /**
     * Check if this loader can provide routes for the current context.
     *
     * Used by RouteBootstrapper to determine which loader to use.
     */
    public function isAvailable() : bool;

    /**
     * Get priority of this loader (higher = preferred).
     *
     * Used when multiple loaders are available to choose the best one.
     */
    public function getPriority() : int;

    /**
     * Get descriptive name of this loader for debugging/logging.
     */
    public function getName() : string;
}