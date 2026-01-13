<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Cache;

use Avax\Container\Features\Operate\Config\ContainerConfig;

/**
 * The "Bridge" between the container and external caching systems.
 *
 * CacheManagerIntegration is responsible for deciding exactly how and
 * where the container's blueprints should be stored based on the
 * application's global configuration. It acts as a Factory that produces
 * {@see PrototypeCache} instances, allowing the container to remain
 * agnostic of whether it's using local files, Redis, or a custom
 * enterprise cache manager.
 *
 * @see     docs/Features/Think/Cache/CacheManagerIntegration.md
 * @see     ContainerConfig The source of truth for cache settings.
 * @see     FilePrototypeCache The most common output of this bridge.
 */
final readonly class CacheManagerIntegration
{
    /**
     * Initializes the integration bridge.
     *
     * @param mixed           $cacheManager An optional external cache manager (e.g. from Laravel/Symfony).
     * @param ContainerConfig $config       The container's own configuration settings.
     */
    public function __construct(
        private mixed           $cacheManager,
        private ContainerConfig $config
    ) {}

    /**
     * Factory method to create the appropriate cache implementation.
     *
     * Currently prioritizing the high-performance FilePrototypeCache using
     * the directory specified in the ContainerConfig.
     *
     * @return PrototypeCache A ready-to-use cache implementation.
     *
     * @see docs/Features/Think/Cache/CacheManagerIntegration.md#method-createprototypecache
     */
    public function createPrototypeCache() : PrototypeCache
    {
        $dir = $this->config->cacheDir;

        if ($dir === null) {
            return new NullPrototypeCache;
        }

        return new FilePrototypeCache(directory: $dir);
    }

    /**
     * Retrieve diagnostic metadata about the current caching setup.
     *
     * @return array{backend_type: string} Information used for debugging and telemetry.
     *
     * @see docs/Features/Think/Cache/CacheManagerIntegration.md#method-getglobalstats
     */
    public function getGlobalStats() : array
    {
        return [
            'backend_type' => $this->cacheManager ? $this->cacheManager::class : 'none',
        ];
    }
}
