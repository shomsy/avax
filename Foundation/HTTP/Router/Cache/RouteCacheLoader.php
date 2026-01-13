<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Cache;

use Avax\Contracts\FilesystemException;
use Avax\Filesystem\Contracts\FilesystemInterface;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouterRegistrar;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

final readonly class RouteCacheLoader
{
    public function __construct(
        private RouterRegistrar        $registrar,
        private RouterRuntimeInterface $router,
        private FilesystemInterface    $filesystem,
        private LoggerInterface        $logger = new NullLogger,
    ) {}

    /**
     * Loads route definitions from JSON cache and registers them into the router.
     *
     * Uses secure JSON deserialization instead of PHP require() to prevent code injection attacks.
     *
     * @param string $cachePath
     * @param string $routesPath
     *
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function load(string $cachePath, string $routesPath) : void
    {
        if (! $this->filesystem->exists(path: $cachePath)) {
            throw new RuntimeException(message: "Route cache file not found: {$cachePath}");
        }

        $metadataPath    = RouteCacheManifest::metadataPath(cachePath: $cachePath);
        $storedManifest  = RouteCacheManifest::fromFile(metadataPath: $metadataPath);
        $currentManifest = RouteCacheManifest::buildFromDirectory(baseDir: $routesPath);

        if ($storedManifest === null || ! $storedManifest->matches(other: $currentManifest)) {
            throw new RuntimeException(message: 'Route cache manifest mismatch; rebuild required.');
        }

        // Validate signature for immutable routing guarantees (v2.1 feature)
        if (! $storedManifest->validateSignatureFile(cachePath: $cachePath)) {
            $this->logger?->warning(message: 'Route cache signature validation failed. Cache may be compromised.', context: [
                'cache_path'  => $cachePath,
                'routes_path' => $routesPath,
            ]);
            throw new RuntimeException(message: 'Route cache signature validation failed. Cache integrity compromised.');
        }

        // Load routes from secure JSON format instead of PHP require()
        $cacheContent = $this->filesystem->get(path: $cachePath);

        try {
            /** @var array<array<string, mixed>> $routes */
            $routes = json_decode($cacheContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new RuntimeException(message: 'Invalid route cache JSON format.', previous: $exception);
        }

        if (! is_array(value: $routes)) {
            throw new RuntimeException(message: 'Invalid route cache: must be an array.');
        }

        foreach ($routes as $definition) {
            if (! is_array($definition)) {
                throw new RuntimeException(message: 'Invalid route in cache.');
            }

            $this->registrar->registerRouteFromCache(definition: RouteDefinition::fromArray(payload: $definition));
        }
    }

    /**
     * Writes the current route definitions to a secure JSON cache file.
     *
     * Uses JSON serialization instead of PHP code generation for security.
     *
     * @param string $cachePath
     * @param string $routesPath
     *
     * @throws \Avax\Contracts\FilesystemException
     */
    public function write(string $cachePath, string $routesPath) : void
    {
        $directory = dirname(path: $cachePath);

        $this->ensureDirectoryIsWritable(directory: $directory);

        $routeDefinitions = $this->router->allRoutes();

        $flattenedRoutes = $routeDefinitions === []
            ? []
            : array_merge(...array_values(array: $routeDefinitions));

        $exportable = [];
        foreach ($flattenedRoutes as $route) {
            if ($route->usesClosure()) {
                $this->logger->warning(message: 'Skipping closure route during cache write.', context: [
                    'path' => $route->path,
                ]);

                continue;
            }

            $exportable[] = $route->toArray();
        }

        if ($exportable === []) {
            throw new RuntimeException(message: 'No cacheable routes available (closures are not cached).');
        }

        // Write secure JSON format instead of PHP code
        try {
            $content = json_encode($exportable, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException $exception) {
            throw new RuntimeException(message: 'Failed to encode route cache as JSON.', previous: $exception);
        }

        $this->filesystem->put(path: $cachePath, content: $content);

        $manifest = RouteCacheManifest::buildFromDirectory(baseDir: $routesPath);
        $manifest->writeTo(metadataPath: RouteCacheManifest::metadataPath(cachePath: $cachePath));

        // Write signature for immutable routing guarantees (v2.1 feature)
        $manifest->writeSignature(cachePath: $cachePath);
    }

    /**
     * Ensures the cache directory is writable.
     *
     *
     * @throws RuntimeException
     */
    private function ensureDirectoryIsWritable(string $directory) : void
    {
        try {
            $this->filesystem->ensureDirectory(path: $directory);
        } catch (FilesystemException) {
            throw new RuntimeException(message: "Cannot create route cache directory: {$directory}");
        }

        // Note: FilesystemInterface doesn't have isWritable check
        // Assuming ensureDirectory makes it writable, or this would need
        // to be handled by the implementation
    }
}