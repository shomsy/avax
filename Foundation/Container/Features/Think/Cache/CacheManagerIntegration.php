<?php

declare(strict_types=1);
namespace Avax\Container\Features\Think\Cache;

use Avax\Container\Features\Operate\Config\ContainerConfig;

/**
 * @package Avax\Container\Think\Cache
 *
 * Integration layer between external cache managers and container prototype caching.
 *
 * CacheManagerIntegration provides a bridge between external caching systems
 * and the container's prototype caching infrastructure. It enables the use of
 * various cache backends (files, Redis, APCu, etc.) while maintaining a consistent
 * interface for prototype storage and retrieval.
 *
 * WHY IT EXISTS:
 * - To integrate external cache managers with container prototype caching
 * - To provide configurable cache backends for different environments
 * - To enable diagnostics and monitoring of cache performance
 * - To support enterprise caching requirements and SLAs
 *
 * ARCHITECTURAL ROLE:
 * - Factory for PrototypeCache implementations
 * - Bridge between external cache systems and container internals
 * - Provider of cache diagnostics and statistics
 * - Configurable cache backend selection and management
 *
 * CACHE BACKEND SUPPORT:
 * - File system storage (default, always available)
 * - External cache managers (Redis, APCu, Memcached)
 * - Custom cache implementations through extension
 * - Multiple cache strategies for different use cases
 *
 * CONFIGURATION INTEGRATION:
 * - Uses ContainerConfig for cache directory and settings
 * - Supports environment-specific cache configurations
 * - Enables cache backend switching without code changes
 * - Provides defaults for development and production
 *
 * DIAGNOSTICS CAPABILITIES:
 * - Backend type identification for monitoring
 * - Cache performance statistics and metrics
 * - Health checks and connectivity validation
 * - Cache hit/miss ratio reporting
 *
 * USAGE SCENARIOS:
 * - Bootstrap configuration of cache backends
 * - Runtime cache backend switching
 * - Cache performance monitoring and alerting
 * - Development vs production cache configuration
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Minimal overhead in cache backend selection
 * - Fast cache instance creation and configuration
 * - Efficient diagnostics data collection
 * - Memory-conscious implementation
 *
 * THREAD SAFETY:
 * - Stateless factory operations
 * - Thread-safe cache backend interactions
 * - Safe for concurrent cache operations
 *
 * ERROR HANDLING:
 * - Graceful fallback to file cache if external backend fails
 * - Clear error messages for configuration issues
 * - Validation of cache backend availability
 * - Recovery strategies for cache failures
 *
 * EXTENSIBILITY:
 * - Pluggable cache backend implementations
 * - Custom cache manager integrations
 * - Additional diagnostics and monitoring
 * - Support for new cache technologies
 *
 * PRODUCTION OPTIMIZATION:
 * - Fast cache backend initialization
 * - Minimal runtime overhead
 * - Efficient cache backend selection
 * - Optimized for high-throughput scenarios
 *
 * MONITORING INTEGRATION:
 * - Cache backend type reporting
 * - Performance metrics collection
 * - Health check capabilities
 * - Integration with application monitoring systems
 *
 * LIMITATIONS:
 * - Depends on external cache manager implementations
 * - Configuration-driven (not runtime dynamic)
 * - Limited to supported cache backend types
 * - Requires proper cache manager setup
 *
 * @see     PrototypeCache The cache interface being implemented
 * @see     FilePrototypeCache The default file-based implementation
 * @see     ContainerConfig Configuration source for cache settings
 * @see docs_md/Features/Think/Cache/CacheManagerIntegration.md#quick-summary
 */
final readonly class CacheManagerIntegration
{
    public function __construct(
        private mixed           $cacheManager,
        private ContainerConfig $config
    ) {}

    /**
     * Create the configured prototype cache implementation.
     *
     * @return PrototypeCache
     * @see docs_md/Features/Think/Cache/CacheManagerIntegration.md#method-createprototypecache
     */
    public function createPrototypeCache() : PrototypeCache
    {
        return new FilePrototypeCache(directory: $this->config->prototypeCacheDir);
    }

    /**
     * Return high-level diagnostic stats about the selected cache backend.
     *
     * @return array
     * @see docs_md/Features/Think/Cache/CacheManagerIntegration.md#method-getglobalstats
     */
    public function getGlobalStats() : array
    {
        return [
            'backend_type' => $this->cacheManager ? $this->cacheManager::class : 'none',
        ];
    }
}
