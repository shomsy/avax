<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Cache;

use Avax\Contracts\FilesystemException;
use Avax\Filesystem\Contracts\AsyncFilesystemInterface;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouterRegistrar;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use function Amp\all;

/**
 * Asynchronous route cache loader for modern PHP runtimes.
 *
 * Provides non-blocking I/O for route cache operations in Swoole, ReactPHP,
 * and other async environments. Falls back to sync operations when async
 * runtime is not available.
 *
 * Usage:
 * ```php
 * $asyncLoader = new AsyncRouteCacheLoader($registrar, $router, $asyncFilesystem);
 * $promise = $asyncLoader->loadAsync($cachePath, $routesPath);
 * $result = await $promise; // In async context
 * ```
 */
final readonly class AsyncRouteCacheLoader
{
    public function __construct(
        private RouterRegistrar          $registrar,
        private RouterRuntimeInterface   $router,
        private AsyncFilesystemInterface $filesystem,
        private LoggerInterface          $logger = new NullLogger,
    ) {}

    /**
     * Asynchronously loads route definitions from serialized cache.
     *
     * @param string $cachePath  Path to the route cache file
     * @param string $routesPath Path to routes directory for manifest validation
     *
     * @return mixed Promise resolving to void
     * @throws \Avax\Contracts\FilesystemException
     */
    public function loadAsync(string $cachePath, string $routesPath) : mixed
    {
        if ($this->filesystem->supportsAsync()) {
            return $this->loadAsyncInternal(cachePath: $cachePath, routesPath: $routesPath);
        }

        // Fallback to sync operation
        return $this->loadSync(cachePath: $cachePath, routesPath: $routesPath);
    }

    /**
     * Internal async load implementation.
     *
     * @throws \Avax\Contracts\FilesystemException
     * @throws \Avax\Contracts\FilesystemException
     */
    private function loadAsyncInternal(string $cachePath, string $routesPath) : mixed
    {
        $metadataPath = RouteCacheManifest::metadataPath(cachePath: $cachePath);

        // Check if files exist asynchronously
        return $this->filesystem->existsAsync(path: $cachePath)->then(
            function ($cacheExists) use ($cachePath, $metadataPath, $routesPath) {
                if (! $cacheExists) {
                    throw new RuntimeException(message: "Route cache file not found: {$cachePath}");
                }

                return $this->filesystem->existsAsync(path: $metadataPath);
            }
        )->then(
            function ($metadataExists) use ($metadataPath, $cachePath, $routesPath) {
                if (! $metadataExists) {
                    throw new RuntimeException(message: "Route cache metadata not found: {$metadataPath}");
                }

                // Load metadata and cache content in parallel
                return all([
                    $this->filesystem->getAsync(path: $metadataPath),
                    $this->filesystem->getAsync(path: $cachePath)
                ]);
            }
        )->then(
            function ($results) use ($routesPath, $cachePath) {
                [$metadataContent, $cacheContent] = $results;

                // Parse and validate manifest
                $storedManifest  = RouteCacheManifest::fromFileContent($metadataContent);
                $currentManifest = RouteCacheManifest::buildFromDirectory(baseDir: $routesPath);

                if ($storedManifest === null || ! $storedManifest->matches($currentManifest)) {
                    throw new RuntimeException(message: 'Route cache manifest mismatch; rebuild required.');
                }

                // Validate signature for immutable routing guarantees (v2.1 feature)
                if (! $storedManifest->validateSignatureFile($cachePath)) {
                    $this->logger->warning(message: 'Route cache signature validation failed. Cache may be compromised.', context: [
                        'cache_path'  => $cachePath,
                        'routes_path' => $routesPath,
                    ]);
                    throw new RuntimeException(message: 'Route cache signature validation failed. Cache integrity compromised.');
                }

                // Load and register routes
                $routes = $this->unserializeRoutes(content: $cacheContent);
                $this->registerRoutes(routes: $routes);

                $this->logger->info(message: 'Async route cache loaded successfully', context: [
                    'cache_path'    => $cachePath,
                    'routes_loaded' => count($routes),
                ]);
            }
        );
    }

    /**
     * Unserializes route data from cache content.
     */
    private function unserializeRoutes(string $content) : array
    {
        // Since we can't eval async, we need to parse the PHP export manually
        // This is a simplified implementation - in practice you'd want more robust parsing
        $routes = require 'data:text/plain;base64,' . base64_encode($content);

        return $routes;
    }

    /**
     * Registers routes with the router.
     */
    private function registerRoutes(array $routes) : void
    {
        foreach ($routes as $definition) {
            if (! is_array($definition)) {
                throw new RuntimeException(message: 'Invalid route in cache.');
            }

            $this->registrar->registerRouteFromCache(definition: RouteDefinition::fromArray(payload: $definition));
        }
    }

    /**
     * Synchronous fallback for load operation.
     */
    private function loadSync(string $cachePath, string $routesPath) : void
    {
        if (! $this->filesystem->exists($cachePath)) {
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
            $this->logger->warning(message: 'Route cache signature validation failed. Cache may be compromised.', context: [
                'cache_path'  => $cachePath,
                'routes_path' => $routesPath,
            ]);
            throw new RuntimeException(message: 'Route cache signature validation failed. Cache integrity compromised.');
        }

        /** @var array<array<string, mixed>> $routes */
        $routes = require $cachePath;

        if (! is_array($routes)) {
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
     * Asynchronously writes route definitions to serialized cache.
     *
     * @param string $cachePath  Path where cache file should be written
     * @param string $routesPath Path to routes directory for manifest creation
     *
     * @return mixed Promise resolving to void
     * @throws \Avax\Contracts\FilesystemException
     * @throws \JsonException
     */
    public function writeAsync(string $cachePath, string $routesPath) : mixed
    {
        if ($this->filesystem->supportsAsync()) {
            return $this->writeAsyncInternal(cachePath: $cachePath, routesPath: $routesPath);
        }

        // Fallback to sync operation
        return $this->writeSync(cachePath: $cachePath, routesPath: $routesPath);
    }

    /**
     * Internal async write implementation.
     *
     * @throws \Avax\Contracts\FilesystemException
     * @throws \Avax\Contracts\FilesystemException
     * @throws \JsonException
     * @throws \Avax\Contracts\FilesystemException
     */
    private function writeAsyncInternal(string $cachePath, string $routesPath) : mixed
    {
        $directory    = dirname($cachePath);
        $metadataPath = RouteCacheManifest::metadataPath(cachePath: $cachePath);

        // Ensure directory exists
        return $this->filesystem->ensureDirectoryAsync(path: $directory)->then(
            function () use ($routesPath, $cachePath, $metadataPath) {
                $routeDefinitions = $this->router->allRoutes();
                $flattenedRoutes  = $this->flattenRoutes(routeDefinitions: $routeDefinitions);
                $exportable       = $this->prepareExportableRoutes(flattenedRoutes: $flattenedRoutes);

                if (empty($exportable)) {
                    throw new RuntimeException(message: 'No cacheable routes available (closures are not cached).');
                }

                $exported = var_export($exportable, true);
                $hash     = sha1($exported);
                $content  = "<?php\n\n/** Auto-generated route cache [sha1: {$hash}]. Do not edit manually. */\n\nreturn {$exported};\n";

                $manifest        = RouteCacheManifest::buildFromDirectory(baseDir: $routesPath);
                $manifestContent = json_encode($manifest->toArray(), JSON_THROW_ON_ERROR);

                // Write cache and metadata in parallel
                return all([
                    $this->filesystem->putAsync(path: $cachePath, content: $content),
                    $this->filesystem->putAsync(path: $metadataPath, content: $manifestContent),
                ])->then(
                    function () use ($manifest, $cachePath) {
                        // Write signature for immutable routing guarantees
                        $manifest->writeSignature(cachePath: $cachePath);

                        $this->logger->info(message: 'Async route cache written successfully', context: [
                            'cache_path' => $cachePath,
                        ]);
                    }
                );
            }
        );
    }

    /**
     * Flattens route definitions for caching.
     */
    private function flattenRoutes(array $routeDefinitions) : array
    {
        return $routeDefinitions === []
            ? []
            : array_merge(...array_values($routeDefinitions));
    }

    /**
     * Prepares routes for export (filters out closures).
     */
    private function prepareExportableRoutes(array $flattenedRoutes) : array
    {
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

        return $exportable;
    }

    /**
     * Synchronous fallback for write operation.
     */
    private function writeSync(string $cachePath, string $routesPath) : void
    {
        $directory = dirname($cachePath);

        $this->ensureDirectoryIsWritable(directory: $directory);

        $routeDefinitions = $this->router->allRoutes();
        $flattenedRoutes  = $this->flattenRoutes(routeDefinitions: $routeDefinitions);
        $exportable       = $this->prepareExportableRoutes(flattenedRoutes: $flattenedRoutes);

        if (empty($exportable)) {
            throw new RuntimeException(message: 'No cacheable routes available (closures are not cached).');
        }

        $exported = var_export($exportable, true);
        $hash     = sha1($exported);
        $content  = "<?php\n\n/** Auto-generated route cache [sha1: {$hash}]. Do not edit manually. */\n\nreturn {$exported};\n";

        $this->filesystem->put($cachePath, $content);

        $manifest = RouteCacheManifest::buildFromDirectory(baseDir: $routesPath);
        $manifest->writeTo(metadataPath: RouteCacheManifest::metadataPath(cachePath: $cachePath));

        // Write signature for immutable routing guarantees (v2.1 feature)
        $manifest->writeSignature(cachePath: $cachePath);
    }

    /**
     * Ensures directory is writable (sync fallback).
     */
    private function ensureDirectoryIsWritable(string $directory) : void
    {
        try {
            $this->filesystem->ensureDirectory($directory);
        } catch (FilesystemException) {
            throw new RuntimeException(message: "Cannot create route cache directory: {$directory}");
        }
    }
}