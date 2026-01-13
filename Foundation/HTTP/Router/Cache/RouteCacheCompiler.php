<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Cache;

use Avax\HTTP\Router\Support\RouteRegistry;
use JsonException;
use RuntimeException;

/**
 * A service responsible for compiling application routes into a single cache file.
 * This compiler improves performance by preloading all the route definitions
 * during runtime instead of dynamically loading them.
 *
 * Supports the serialization of Closure-based route actions using Laravel\SerializableClosure.
 */
final readonly class RouteCacheCompiler
{
    /**
     * Compiles route files from a specific directory into a unified PHP cache file.
     *
     * This process consists of discovering route definition files (`*.routes.php`),
     * invoking all buffered route builders, serializing binding logic, and finally
     * writing the compiled routes in a compact serialized format for subsequent execution.
     *
     * @param string $directory  The absolute path to the directory containing `*.routes.php` files.
     * @param string $outputFile The absolute file path where the compiled routes cache will be stored.
     *
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function compile(string $directory, string $outputFile) : void
    {
        $routes = [];

        $locator  = new RouteFileLocator;
        $registry = new RouteRegistry;

        foreach ($locator->discover(baseDir: $directory) as $file) {
            $registry->scoped(closure: static function () use ($file, &$routes, $registry) : void {
                require $file->getPathname();

                $builders = $registry->flush();

                if (empty($builders)) {
                    return;
                }

                foreach ($builders as $builder) {
                    $route = $builder->build();

                    if ($route->usesClosure()) {
                        continue;
                    }

                    $routes[] = $route->toArray();
                }
            });
        }

        if (empty($routes)) {
            throw new RuntimeException(message: 'No routes were registered. Check your route files.');
        }

        $cacheContent = $this->generateCacheFileContent(routes: $routes);

        if (! file_put_contents(filename: $outputFile, data: $cacheContent)) {
            throw new RuntimeException(message: "Failed to write route cache to: {$outputFile}");
        }

        $manifest = RouteCacheManifest::buildFromDirectory(baseDir: $directory);
        $manifest->writeTo(metadataPath: RouteCacheManifest::metadataPath(cachePath: $outputFile));
    }

    /**
     * Generates the JSON content to be written in the cache file.
     *
     * Uses secure JSON format instead of PHP code to prevent code injection attacks.
     *
     * @param array $routes A list of route definition arrays.
     *
     * @return string The resultant JSON file's content as a string.
     */
    private function generateCacheFileContent(array $routes) : string
    {
        try {
            return json_encode($routes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $exception) {
            throw new RuntimeException(message: 'Failed to encode route cache as JSON.', previous: $exception);
        }
    }
}