<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\BootstrapRoutes\Disk;

use Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteCollection;
use Avax\HTTP\Router\System\Flows\BootstrapRoutes\Source\RouteSourceLoaderInterface;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteCollector;
use RuntimeException;
use Throwable;

/**
 * Route loader that loads routes from PHP files on disk.
 *
 * Executes route definition files within a scoped context to prevent
 * global state pollution and ensure isolation between different route sources.
 */
final readonly class DiskRouteLoader implements RouteSourceLoaderInterface
{
    private string $routesPath;

    public function __construct(
        string $routesPath
    )
    {
        $this->routesPath = $routesPath;
    }

    /**
     * Load routes from disk files into the collection.
     *
     * Creates a scoped collector context to prevent global state pollution
     * and executes route definition files safely with instance-based isolation.
     *
     * @throws RuntimeException If route file execution fails
     */
    public function loadInto(RouteCollection $collection) : void
    {
        if (! $this->isAvailable()) {
            throw new RuntimeException(message: "Routes file not found: {$this->routesPath}");
        }

        // Execute routes in scoped collector context to prevent global pollution
        $collector = RouteCollector::scoped(closure: function (RouteCollector $collector) : void {
            try {
                // Read file content and execute DSL
                $code = file_get_contents(filename: $this->routesPath);
                if ($code === false) {
                    throw new RuntimeException(message: "Cannot read routes file: {$this->routesPath}");
                }

                $collector->executeDsl(code: $code);
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    message : "Failed to load routes from {$this->routesPath}: " . $exception->getMessage(),
                    code    : 0,
                    previous: $exception
                );
            }
        });

        // Transfer loaded routes to collection
        foreach ($collector->flush() as $routeBuilder) {
            try {
                $route = $routeBuilder->build();
                $collection->addRoute(route: $route);
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    message : 'Failed to build route from file: ' . $exception->getMessage(),
                    code    : 0,
                    previous: $exception
                );
            }
        }
    }

    /**
     * Check if routes file exists and is readable.
     */
    public function isAvailable() : bool
    {
        return is_file(filename: $this->routesPath) && is_readable(filename: $this->routesPath);
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
