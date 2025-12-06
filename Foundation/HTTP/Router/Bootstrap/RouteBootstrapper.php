<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Bootstrap;

use FilesystemIterator;
use Avax\Config\Architecture\DDD\AppPath;
use Avax\HTTP\Router\Cache\RouteCacheLoader;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Support\RouteCollector;
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
     * @var RouteCacheLoader  $routeCacheLoader  Handles route caching operations.
     * @var HttpRequestRouter $httpRequestRouter Responsible for registering and managing application routes.
     * @var LoggerInterface   $logger            Logs important messages and errors.
     */
    public function __construct(
        private RouteCacheLoader  $routeCacheLoader,
        private HttpRequestRouter $httpRequestRouter,
        private LoggerInterface   $logger,
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
        // Retrieve the paths for route cache and routes directory.
        $cachePath  = AppPath::ROUTE_CACHE_PATH->get();
        $routesPath = AppPath::ROUTES_PATH->get();

        try {
            // Check if the cache file exists and is valid, load routes from it if true.
            if ($this->isCacheAvailable(cachePath: $cachePath)) {
                $this->loadRoutesFromCache(cachePath: $cachePath);
            } else {
                // Otherwise, load routes from disk and generate a new cache file.
                $this->loadRoutesFromDiskAndCache(routesPath: $routesPath, cachePath: $cachePath);
            }

            // Load closure-based routes from disk even if cache was used.
            $this->loadClosureRoutesFromDisk(routesPath: $routesPath);
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
    private function isCacheAvailable(string $cachePath) : bool
    {
        return is_file($cachePath) && is_readable($cachePath);
    }

    /**
     * Loads routes from the cache file.
     *
     * @param string $cachePath Path to the route cache file.
     */
    private function loadRoutesFromCache(string $cachePath) : void
    {
        // Use the route cache loader to load cached routes.
        $this->routeCacheLoader->load(cachePath: $cachePath);

        // Log the successful loading of cached routes.
        $this->logger->info(message: '✅ Route cache loaded.', context: ['cache' => $cachePath]);
    }

    /**
     * Loads routes from disk and generates a new cache file for future use.
     *
     * @param string $routesPath Path to the directory containing route definition files.
     * @param string $cachePath  Path to where the new cache file should be written.
     */
    private function loadRoutesFromDiskAndCache(string $routesPath, string $cachePath) : void
    {
        // Load routes from disk-based files.
        $this->loadRoutesFromDisk(baseDir: $routesPath);

        // Write the loaded routes to a cache file.
        $this->routeCacheLoader->write(cachePath: $cachePath);

        // Log the creation of a new route cache.
        $this->logger->info(message: '📦 Route cache created from disk.', context: [
            'source' => $routesPath,
            'cache'  => $cachePath,
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
    private function loadRoutesFromDisk(string $baseDir) : void
    {
        // Reset the route collector to ensure no stale routes remain in memory.
        RouteCollector::reset();

        // Iterate through all route files in the base directory.
        foreach ($this->getRouteFilesFromDirectory(baseDir: $baseDir) as $file) {
            $this->processRouteFile(file: $file);
        }

        // Register any fallback route defined during route processing.
        $this->registerFallbackRoute();
    }

    /**
     * Retrieves all `.routes.php` files recursively from the specified directory.
     *
     * @param string $baseDir Directory to search for route files.
     *
     * @return list<SplFileInfo> A list of route files (instances of `SplFileInfo`).
     * @throws RuntimeException Thrown if the directory is inaccessible or unreadable.
     */
    private function getRouteFilesFromDirectory(string $baseDir) : array
    {
        // Ensure the provided directory exists and is readable.
        if (! is_dir($baseDir) || ! is_readable($baseDir)) {
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
            array   : iterator_to_array($iterator),
            callback: static fn(SplFileInfo $file) : bool => $file->isFile()
                                                             && $file->isReadable()
                                                             && preg_match(
                                                                 '/\.routes\.php$|^routes\.php$/',
                                                                 $file->getFilename()
                                                             )
        );

        // Ensure a returned array is indexed sequentially
        return array_values($routeFiles); // Avoids gaps in array keys


    }

    /**
     * Processes a route file and registers all contained routes with the router.
     *
     * @param SplFileInfo $file Route file to process.
     */
    private function processRouteFile(SplFileInfo $file) : void
    {
        // Include the route file to evaluate its contents in the current context.
        require_once $file->getPathname();

        // Flush buffered routes from the collector and register them with the router.
        foreach (RouteCollector::flushBuffered() as $routeBuilder) {
            $this->httpRequestRouter->registerRoute(
                method       : $routeBuilder->method,
                path         : $routeBuilder->path,
                action       : $routeBuilder->action,
                middleware   : $routeBuilder->middleware,
                name         : $routeBuilder->name,
                constraints  : $routeBuilder->constraints,
                defaults     : $routeBuilder->defaults,
                domain       : $routeBuilder->domain,
                attributes   : $routeBuilder->attributes,
                authorization: $routeBuilder->authorization,
            );
        }
    }

    /**
     * Registers a fallback route if one is defined within `RouteCollector`.
     */
    private function registerFallbackRoute() : void
    {
        // Check if a fallback route exists in the collector.
        $fallback = RouteCollector::getFallback();

        // If a fallback route exists, register it with the router.
        if ($fallback !== null) {
            $this->httpRequestRouter->fallback(handler: $fallback);
        }
    }

    /**
     * Loads and registers closure-based routes directly from disk.
     *
     * @param string $routesPath Path to the directory containing route definition files.
     */
    private function loadClosureRoutesFromDisk(string $routesPath) : void
    {
        // Reuse the `loadRoutesFromDisk` method to handle closure-based route files.
        $this->loadRoutesFromDisk(baseDir: $routesPath);

        // Log the successful loading of closure-based routes.
        $this->logger->info(message: '🔁 Closure-based routes loaded from disk.', context: [
            'directory' => $routesPath,
        ]);
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
        $this->logger->critical(message: '🔥 Route bootstrap failed.', context: [
            'exception'  => $exception->getMessage(),
            'trace'      => $exception->getTraceAsString(),
            'cache_path' => $cachePath,
            'routes_dir' => $routesPath,
        ]);
    }
}