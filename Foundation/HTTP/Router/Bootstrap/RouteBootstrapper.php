<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Bootstrap;

use Avax\Config\Architecture\DDD\AppPath;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Cache\RouteCacheLoader;
use Avax\HTTP\Router\Cache\RouteCacheManifest;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Snapshots\RouterSnapshot;
use Avax\HTTP\Router\Support\RouteRegistry;
use Avax\HTTP\Router\Support\RouterBootstrapState;
use Closure;
use FilesystemIterator;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Throwable;

/**
 * Handles the bootstrapping of application routes.
 *
 * Responsibilities:
 * - Ensure routes are loaded efficiently and securely.
 * - Load routes from cache, if available, for improved performance.
 * - Fallback to loading routes from disk-based DSL/closure definitions.
 * - Support error handling, logging, and other critical processes.
 *
 * This class emphasizes secure, clean, and efficient route bootstrapping.
 */
final readonly class RouteBootstrapper
{
    /**
     * @var RouteCacheLoader Handles route caching operations.
     * @var HttpRequestRouter Responsible for registering and managing application routes.
     * @var RouterBootstrapState Thread-safe bootstrap state management.
     * @var RouteRegistry Route registry for isolated route collection.
     * @var LoggerInterface Logs important messages and errors.
     */
    public function __construct(
        private RouteCacheLoader     $routeCacheLoader,
        private HttpRequestRouter    $httpRequestRouter,
        private RouterBootstrapState $bootstrapState,
        private RouteRegistry        $routeRegistry,
        private LoggerInterface      $logger,
    ) {}

    /**
     * Bootstraps application routes.
     *
     * - Attempts to load routes from the cache file if available.
     * - Falls back to loading routes from disk files if the cache is absent or outdated.
     * - Always loads closure-based routes directly from disk to ensure runtime correctness.
     *
     * @throws Throwable Any exception encountered during route bootstrapping is logged and re-thrown.
     */
    public function bootstrap() : void
    {
        $this->bootstrapState->ensureNotBooted();

        // Retrieve the paths for route cache and routes directory.
        $cachePath   = AppPath::ROUTE_CACHE_PATH->get();
        $routesPath  = AppPath::ROUTES_PATH->get();
        $cacheLoaded = false;

        try {
            // DECISION TRACE: Bootstrap path selection
            $cacheAvailable = $this->isCacheAvailable(cachePath: $cachePath, routesPath: $routesPath);
            $this->logger->debug(message: 'Route bootstrap decision', context: [
                'cache_available' => $cacheAvailable,
                'cache_path'      => $cachePath,
                'routes_path'     => $routesPath,
            ]);

            // Check if the cache file exists and is valid, load routes from it if true.
            if ($cacheAvailable) {
                $this->bootstrapState->markSource(source: 'cache');
                $this->logger->info(message: 'Route bootstrap: using cache', context: ['cache_path' => $cachePath]);
                $this->loadRoutesFromCache(cachePath: $cachePath, routesPath: $routesPath);
                $cacheLoaded = true;

                // When cache is loaded, we still need to execute closure-based routes
                // but only those that were not cached (closures are never cached)
                $this->logger->debug(message: 'Route bootstrap: loading closure routes from disk');
                $this->loadRoutesFromDisk(baseDir: $routesPath, closuresOnly: true);
            } else {
                $this->bootstrapState->markSource(source: 'disk');
                $this->logger->info(message: 'Route bootstrap: building from disk', context: ['routes_path' => $routesPath]);
                // Otherwise, load ALL routes from disk and generate a new cache file.
                // This includes both cacheable routes and closures in one pass.
                $this->loadRoutesFromDiskAndCache(routesPath: $routesPath, cachePath: $cachePath);
            }

            // Log final bootstrap statistics
            $routesCount = count($this->httpRequestRouter->allRoutes());
            $this->logger->info(message: 'Route bootstrap completed.', context: [
                'routes_count' => $routesCount,
                'source'       => $this->bootstrapState->getSource()
            ]);

            // Export router snapshot for reproducibility and auditing (v2.1 feature)
            $this->exportRouterSnapshot(routesPath: $routesPath);
        } catch (Throwable $exception) {
            // Handle and log critical errors during route setup, then re-throw the exception.
            $this->handleCriticalError(exception: $exception, cachePath: $cachePath, routesPath: $routesPath);
            throw $exception;
        }
    }

    /**
     * Validates the availability of the route cache file.
     *
     * @param string $cachePath Absolute path of the cache file.
     *
     * @return bool Returns true if the cache file exists and is readable.
     */
    private function isCacheAvailable(string $cachePath, string $routesPath) : bool
    {
        if (! is_file(filename: $cachePath) || ! is_readable(filename: $cachePath)) {
            return false;
        }

        $storedManifest = RouteCacheManifest::fromFile(
            metadataPath: RouteCacheManifest::metadataPath(cachePath: $cachePath)
        );

        if ($storedManifest === null) {
            return false;
        }

        $currentManifest = RouteCacheManifest::buildFromDirectory(baseDir: $routesPath);

        if (! $storedManifest->matches(other: $currentManifest)) {
            return false;
        }

        return (filemtime(filename: $cachePath) ?: 0) >= $this->getRoutesLastModified(baseDir: $routesPath);
    }

    /**
     * Returns latest modification time inside routes directory (recursive).
     */
    private function getRoutesLastModified(string $baseDir) : int
    {
        if (! is_dir(filename: $baseDir)) {
            return 0;
        }

        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(
                directory: $baseDir,
                flags    : FilesystemIterator::SKIP_DOTS
            )
        );

        $latest = 0;
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $latest = max($latest, $file->getMTime());
            }
        }

        return $latest;
    }

    /**
     * Loads routes from the cache file.
     *
     * @param string $cachePath Path to the route cache file.
     */
    private function loadRoutesFromCache(string $cachePath, string $routesPath) : void
    {
        // Use the route cache loader to load cached routes.
        $this->routeCacheLoader->load(cachePath: $cachePath, routesPath: $routesPath);

        // Log the successful loading of cached routes.
        $this->logger->info(message: 'Route cache loaded.', context: [
            'cache'  => $cachePath,
            'source' => $this->bootstrapState->getSource()
        ]);
    }

    /**
     * Loads and registers routes from disk route definition files.
     *
     * - Iterates over disk route files and registers their routes with the router.
     * - Clears any existing buffered route definitions prior to registration.
     * - Registers the fallback route, if it exists.
     *
     * @param string $baseDir Base directory containing route definition files.
     */
    private function loadRoutesFromDisk(string $baseDir, bool $closuresOnly = false) : void
    {
        $this->routeRegistry->scoped(function () use ($baseDir, $closuresOnly) : void {
            foreach ($this->getRouteFilesFromDirectory(baseDir: $baseDir) as $file) {
                $this->processRouteFile(file: $file, closuresOnly: $closuresOnly);
            }

            $this->registerFallbackRoute(closuresOnly: $closuresOnly);
        });
    }

    /**
     * Retrieves all `.routes.php` files recursively from the specified directory.
     *
     * @param string $baseDir Directory to search for route files.
     *
     * @return list<SplFileInfo> A list of route files (instances of `SplFileInfo`).
     *
     * @throws RuntimeException Thrown if the directory is inaccessible or unreadable.
     */
    private function getRouteFilesFromDirectory(string $baseDir) : array
    {
        // Ensure the provided directory exists and is readable.
        if (! is_dir(filename: $baseDir) || ! is_readable(filename: $baseDir)) {
            throw new RuntimeException(message: "Routes directory '{$baseDir}' is not accessible or readable.");
        }

        // Create a recursive iterator to find all files within the route directory.
        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(
                directory: $baseDir,
                flags    : FilesystemIterator::SKIP_DOTS
            )
        );

        // Filter and return files that end with `.routes.php`, or just 'routes.php'
        $routeFiles = array_filter(
            array   : iterator_to_array(iterator: $iterator),
            callback: static fn(SplFileInfo $file) : bool => $file->isFile()
                && $file->isReadable()
                && preg_match(
                    pattern: '/\.routes\.php$|^routes\.php$/',
                    subject: $file->getFilename()
                )
        );

        // Ensure a returned array is indexed sequentially
        return array_values(array: $routeFiles); // Avoids gaps in array keys

    }

    /**
     * Processes a route file and registers all contained routes with the router.
     *
     * @param SplFileInfo $file Route file to process.
     * @param bool        $closuresOnly
     *
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    private function processRouteFile(SplFileInfo $file, bool $closuresOnly = false) : void
    {
        // Include the route file in an isolated scope to prevent variable leakage
        $this->requireIsolated(path: $file->getPathname());

        // Flush buffered routes from the registry and register them with the router.
        foreach ($this->routeRegistry->flush() as $routeBuilder) {
            $definition = $routeBuilder->build();

            if ($closuresOnly && ! $definition->usesClosure()) {
                continue;
            }

            $this->httpRequestRouter->registerRoute(
                method       : $definition->method,
                path         : $definition->path,
                action       : $definition->action,
                middleware   : $definition->middleware,
                name         : $definition->name,
                constraints  : $definition->constraints,
                defaults     : $definition->defaults,
                domain       : $definition->domain,
                attributes   : $definition->attributes,
                authorization: $definition->authorization,
            );
        }
    }

    /**
     * Requires a file in an isolated scope to prevent variable leakage.
     *
     * This prevents route definition files from accessing or modifying
     * variables in the calling scope, ensuring clean separation.
     *
     * @param string $path The file path to require
     */
    private function requireIsolated(string $path) : void
    {
        (static function () use ($path) : void {
            require $path;
        })();
    }

    /**
     * Registers a fallback route if one is defined within the registry.
     */
    private function registerFallbackRoute(bool $closuresOnly = false) : void
    {
        // Check if a fallback route exists in the registry.
        $fallback = $this->routeRegistry->getFallback();

        if ($fallback !== null && (! $closuresOnly || $fallback instanceof Closure)) {
            $this->httpRequestRouter->fallback(handler: $fallback);
        }
    }

    /**
     * Loads routes from disk and generates a new cache file for future use.
     *
     * @param string $routesPath Path to the directory containing route definition files.
     * @param string $cachePath  Path to where the new cache file should be written.
     *
     * @throws \Avax\Contracts\FilesystemException
     */
    private function loadRoutesFromDiskAndCache(string $routesPath, string $cachePath) : void
    {
        // Load routes from disk-based files.
        $this->loadRoutesFromDisk(baseDir: $routesPath);

        // Write the loaded routes to a cache file.
        $this->routeCacheLoader->write(cachePath: $cachePath, routesPath: $routesPath);

        // Log the creation of a new route cache.
        $this->logger->info(message: 'Route cache created from disk.', context: [
            'source' => $routesPath,
            'cache'  => $cachePath,
        ]);
    }

    /**
     * Exports router configuration snapshot for reproducibility and auditing.
     *
     * Creates immutable snapshots after successful bootstrap for:
     * - Change tracking and rollback capabilities
     * - Governance and regulatory compliance
     * - Configuration auditing across environments
     *
     * @param string $routesPath Path to routes directory for context
     */
    private function exportRouterSnapshot(string $routesPath) : void
    {
        try {
            // Create a router interface wrapper to access all routes
            $routerInterface = new class($this->httpRequestRouter) implements RouterRuntimeInterface {
                public function __construct(private HttpRequestRouter $router) {}

                public function resolve(Request $request) : ResponseInterface
                {
                    // Not needed for snapshot
                    throw new RuntimeException(message: 'Not implemented');
                }

                public function getRouteByName(string $name) : RouteDefinition
                {
                    return $this->router->getByName(name: $name);
                }

                public function allRoutes() : array
                {
                    return $this->router->allRoutes();
                }
            };

            $snapshot = RouterSnapshot::capture(router: $routerInterface, context: [
                'routes_path'    => $routesPath,
                'bootstrap_time' => date('c'),
            ]);

            // Export to standard location
            $snapshotPath = dirname($routesPath) . '/router-snapshot.json';
            $snapshot->exportToFile(path: $snapshotPath);

            $this->logger->info(message: 'Router snapshot exported for reproducibility', context: [
                'snapshot_path' => $snapshotPath,
                'routes_count'  => $snapshot->metadata['total_routes'],
                'checksum'      => substr($snapshot->checksum, 0, 16) . '...',
            ]);

        } catch (Throwable $exception) {
            // Don't fail bootstrap if snapshot fails, just log
            $this->logger->warning(message: 'Failed to export router snapshot', context: [
                'exception'   => $exception->getMessage(),
                'routes_path' => $routesPath,
            ]);
        }
    }

    /**
     * Handles critical errors encountered during route bootstrapping.
     *
     * - Logs the exception and its context to assist debugging.
     *
     * @param Throwable $exception  Exception encountered.
     * @param string    $cachePath  Path to the route cache file.
     * @param string    $routesPath Path to the directory containing route files.
     */
    private function handleCriticalError(Throwable $exception, string $cachePath, string $routesPath) : void
    {
        $this->logger->critical(message: 'Route bootstrap failed.', context: [
            'exception'  => $exception,
            'cache_path' => $cachePath,
            'routes_dir' => $routesPath,
        ]);
    }

    /**
     * Loads and registers closure-based routes directly from disk.
     *
     * @param string $routesPath Path to the directory containing route definition files.
     */
    private function loadClosureRoutesFromDisk(string $routesPath) : void
    {
        // Require route files in isolated scope to execute DSL
        foreach ($this->getRouteFilesFromDirectory(baseDir: $routesPath) as $file) {
            $this->requireIsolated(path: $file->getPathname());
        }

        // Log the successful loading of closure-based routes.
        $this->logger->info(message: 'Closure-based routes loaded from disk.', context: [
            'directory' => $routesPath,
        ]);
    }
}