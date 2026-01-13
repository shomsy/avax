<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * Route loader that loads routes from PHP files on disk.
 *
 * Executes route definition files within a scoped context to prevent
 * global state pollution and ensure isolation between different route sources.
 */
final readonly class DiskRouteLoader implements RouteSourceLoaderInterface
{
    public function __construct(
        private string $routesPath
    ) {}

    /**
     * Load routes from disk files into the collection.
     *
     * Creates a scoped collector context to prevent global state pollution
     * and executes route definition files safely with instance-based isolation.
     *
     * @throws \RuntimeException If route file execution fails
     */
    public function loadInto(RouteCollection $collection) : void
    {
        if (! $this->isAvailable()) {
            throw new \RuntimeException("Routes file not found: {$this->routesPath}");
        }

        // Execute routes in scoped collector context to prevent global pollution
        $collector = RouteCollector::scoped(function (RouteCollector $collector) : void {
            try {
                // Read file content and execute DSL
                $code = file_get_contents($this->routesPath);
                if ($code === false) {
                    throw new \RuntimeException("Cannot read routes file: {$this->routesPath}");
                }

                $collector->executeDsl($code);
            } catch (\Throwable $exception) {
                throw new \RuntimeException(
                    "Failed to load routes from {$this->routesPath}: " . $exception->getMessage(),
                    0,
                    $exception
                );
            }
        });

        // Transfer loaded routes to collection
        foreach ($collector->flush() as $routeBuilder) {
            try {
                $route = $routeBuilder->build();
                $collection->addRoute($route);
            } catch (\Throwable $exception) {
                throw new \RuntimeException(
                    'Failed to build route from file: ' . $exception->getMessage(),
                    0,
                    $exception
                );
            }
        }
    }

    /**
     * Check if routes file exists and is readable.
     */
    public function isAvailable() : bool
    {
        return is_file($this->routesPath) && is_readable($this->routesPath);
    }

    /**
     * Get loader priority (disk has medium priority, used when cache unavailable).
     */
    public function getPriority() : int
    {
        return 50; // Medium priority - fallback when cache not available
    }

    /**
     * Get descriptive loader name.
     */
    public function getName() : string
    {
        return 'disk';
    }
}