<?php

declare(strict_types=1);
namespace Avax\Container\Features\Think\Cache;

use Avax\Container\Features\Think\Model\ServicePrototype;

/**
 * @package Avax\Container\Think\Cache
 *
 * Interface for caching service prototype analysis results.
 *
 * PrototypeCache provides a strategy pattern for persisting ServicePrototype
 * instances across requests. This enables expensive reflection analysis to be
 * performed once and reused, significantly improving performance in production.
 *
 * IMPLEMENTATIONS:
 * - FilePrototypeCache: Persistent file-based storage
 * - MemoryPrototypeCache: In-memory storage for testing
 * - ApcPrototypeCache: APC/APCu shared memory storage
 * - RedisPrototypeCache: Distributed Redis storage
 *
 * CACHE STRATEGIES:
 * - Atomic writes to prevent corruption during updates
 * - Serialization with type safety and integrity checks
 * - TTL support for cache invalidation
 * - Compression for reduced storage footprint
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Fast lookups with O(1) complexity
 * - Lazy loading of cached data
 * - Minimal memory overhead for metadata
 * - Background refresh capabilities
 *
 * THREAD SAFETY:
 * Implementations should be thread-safe for concurrent access.
 * File-based caches should use atomic rename operations.
 *
 * CACHE INVALIDATION:
 * - Manual clearing via clear() method
 * - TTL-based expiration
 * - Dependency tracking for automatic invalidation
 *
 * @see     FilePrototypeCache For production file-based caching
 * @see     ServicePrototype For the cached data structure
 * @see docs_md/Features/Think/Cache/PrototypeCache.md#quick-summary
 */
interface PrototypeCache
{
    /**
     * Retrieves a cached service prototype by class name.
     *
     * Returns the cached ServicePrototype if available, or null if not found
     * or if the cache entry has expired/invalidated.
     *
     * @param string $class The fully qualified class name to retrieve
     *
     * @return \Avax\Container\Features\Think\Model\ServicePrototype|null The cached prototype or null
     * @see docs_md/Features/Think/Cache/PrototypeCache.md#method-get
     */
    public function get(string $class) : ServicePrototype|null;

    /**
     * Stores a service prototype in the cache.
     *
     * Persists the ServicePrototype for future retrieval. Implementations
     * should use atomic operations to prevent cache corruption.
     *
     * @param string                                                $class     The class name as cache key
     * @param \Avax\Container\Features\Think\Model\ServicePrototype $prototype The prototype to cache
     *
     * @return void
     * @see docs_md/Features/Think/Cache/PrototypeCache.md#method-set
     */
    public function set(string $class, ServicePrototype $prototype) : void;

    /**
     * Checks if a prototype exists in the cache.
     *
     * @param string $class The class name to check
     *
     * @return bool True if cached, false otherwise
     * @see docs_md/Features/Think/Cache/PrototypeCache.md#method-has
     */
    public function has(string $class) : bool;

    /**
     * Removes a specific prototype from the cache.
     *
     * @param string $class The class name to remove
     *
     * @return bool True if removed, false if not found
     * @see docs_md/Features/Think/Cache/PrototypeCache.md#method-delete
     */
    public function delete(string $class) : bool;

    /**
     * Clears all cached prototypes.
     *
     * Removes all entries from the cache. Useful for development,
     * configuration changes, or memory management.
     *
     * @return void
     * @see docs_md/Features/Think/Cache/PrototypeCache.md#method-clear
     */
    public function clear() : void;

    /**
     * Gets the number of cached prototypes.
     *
     * @return int The cache size
     * @see docs_md/Features/Think/Cache/PrototypeCache.md#method-count
     */
    public function count() : int;

    /**
     * Checks if a prototype exists without loading it.
     *
     * Fast existence check that avoids deserialization overhead.
     * Useful for validation and statistics without full prototype loading.
     *
     * @param string $class The class name to check
     *
     * @return bool True if prototype exists in cache, false otherwise
     * @see docs_md/Features/Think/Cache/PrototypeCache.md#method-prototypeexists
     */
    public function prototypeExists(string $class) : bool;
}
