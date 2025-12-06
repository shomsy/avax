<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Cache;

use Avax\Facade\Facades\Storage;
use Avax\HTTP\Router\Router;
use Avax\HTTP\Router\Routing\RouteDefinition;
use RuntimeException;

final class RouteCacheLoader
{
    public function __construct(
        private readonly Router $router
    ) {}

    /**
     * Loads route definitions from serialized cache and registers them into the router.
     *
     * @param string $cachePath
     *
     * @throws RuntimeException
     */
    public function load(string $cachePath) : void
    {
        if (! Storage::exists(path: $cachePath)) {
            throw new RuntimeException(message: "Route cache file not found: {$cachePath}");
        }

        /** @var array<RouteDefinition> $routes */
        $routes = require $cachePath;

        if (! is_array($routes)) {
            throw new RuntimeException(message: "Invalid route cache: must be an array.");
        }

        foreach ($routes as $definition) {
            if (! $definition instanceof RouteDefinition) {
                throw new RuntimeException(message: "Invalid route in cache.");
            }

            $this->router->registerRouteFromCache(definition: $definition);
        }
    }

    /**
     * Writes the current route definitions to a serialized cache file.
     *
     * @param string $cachePath
     *
     * @throws RuntimeException
     */
    public function write(string $cachePath) : void
    {
        $directory = dirname($cachePath);

        $this->ensureDirectoryIsWritable(directory: $directory);

        $routeDefinitions = $this->router->allRoutes();

        $flattenedRoutes = array_merge(...array_values($routeDefinitions));

        // 🧼 Remove any route that uses a Closure action
        $serializableRoutes = array_filter(
            $flattenedRoutes,
            static fn(RouteDefinition $route) : bool => ! $route->usesClosure()
        );

        $exported = var_export($serializableRoutes, true);
        $hash     = sha1($exported);
        $content  = "<?php\n\n/** Auto-generated route cache [sha1: {$hash}]. Do not edit manually. */\n\nreturn {$exported};\n";

        if (! Storage::write(path: $cachePath, content: $content)) {
            throw new RuntimeException(message: "Failed to write route cache to: {$cachePath}");
        }
    }


    /**
     * Ensures the cache directory is writable.
     *
     * @param string $directory
     *
     * @throws RuntimeException
     */
    private function ensureDirectoryIsWritable(string $directory) : void
    {
        if (! Storage::exists(path: $directory) && ! Storage::createDirectory(directory: $directory)) {
            throw new RuntimeException(message: "Cannot create route cache directory: {$directory}");
        }

        if (! Storage::isWritable(path: $directory)) {
            throw new RuntimeException(message: "Route cache directory is not writable: {$directory}");
        }
    }
}
