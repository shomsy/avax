<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\Filesystem\Contracts\FilesystemInterface;

/**
 * Route loader that loads routes from compiled cache files.
 *
 * Validates cache integrity using SHA256 signatures before loading,
 * ensuring cache poisoning protection and deterministic behavior.
 */
final readonly class CachedRouteLoader implements RouteSourceLoaderInterface
{
    public function __construct(
        private string $cachePath,
        private FilesystemInterface $filesystem,
        private RouteCacheManifest $manifest
    ) {}

    /**
     * Load routes from cache into the collection.
     *
     * Performs integrity validation before loading to prevent cache poisoning.
     *
     * @throws \RuntimeException If cache is invalid or corrupted
     */
    public function loadInto(RouteCollection $collection) : void
    {
        if (! $this->isAvailable()) {
            throw new \RuntimeException('Cache file not available or invalid');
        }

        // Validate cache integrity before loading
        if (! $this->manifest->validateSignatureFile($this->cachePath)) {
            throw new \RuntimeException('Cache signature validation failed - possible tampering detected');
        }

        // Load and validate JSON content
        $cacheContent = $this->filesystem->get(path: $this->cachePath);

        try {
            /** @var array<array<string, mixed>> $routesData */
            $routesData = json_decode($cacheContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Invalid cache JSON format', 0, $exception);
        }

        if (! is_array($routesData)) {
            throw new \RuntimeException('Cache file does not contain valid route array');
        }

        // Populate collection with cached routes
        foreach ($routesData as $routeData) {
            if (! is_array($routeData)) {
                throw new \RuntimeException('Invalid route data in cache');
            }

            try {
                $route = RouteDefinition::fromArray($routeData);
                $collection->addRoute($route);
            } catch (\Throwable $exception) {
                throw new \RuntimeException(
                    'Failed to load route from cache: ' . $exception->getMessage(),
                    0,
                    $exception
                );
            }
        }
    }

    /**
     * Check if cached routes are available and valid.
     * Includes trust boundary verification.
     */
    public function isAvailable() : bool
    {
        if (! $this->filesystem->exists(path: $this->cachePath)) {
            return false;
        }

        // Verify cache integrity before considering it available
        if (! $this->manifest->validateSignatureFile($cachePath: $this->cachePath)) {
            // Log cache corruption but don't throw - allow fallback to disk loading
            error_log("Route cache signature validation failed for {$this->cachePath} - cache may be corrupted");
            return false;
        }

        return true;
    }

    /**
     * Get loader priority (cache has highest priority for performance).
     */
    public function getPriority() : int
    {
        return 100; // Highest priority - prefer cache when available
    }

    /**
     * Get descriptive loader name.
     */
    public function getName() : string
    {
        return 'cached';
    }
}