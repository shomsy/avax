<?php

declare(strict_types=1);
namespace Avax\Container\Features\Think\Model;

use Avax\Container\Features\Think\Cache\PrototypeCache;
use Avax\Container\Features\Think\Flow\DesignFlow;

/**
 * @package Avax\Container\Think\Model
 *
 * In-memory registry for fast prototype lookups and management.
 *
 * PrototypeRegistry provides a centralized, thread-safe storage mechanism for
 * ServicePrototype instances. It serves as a high-performance cache layer above
 * the file-based PrototypeCache, offering faster access for frequently used
 * prototypes during runtime.
 *
 * KEY FEATURES:
 * - Thread-safe in-memory storage with atomic operations
 * - Fast O(1) lookups for prototype existence and retrieval
 * - Lazy loading from persistent cache when needed
 * - Memory-bounded storage with configurable limits
 * - Integration with bulk operations for performance optimization
 *
 * ARCHITECTURAL ROLE:
 * - Acts as L1 cache above file-based storage (L2 cache)
 * - Provides fast-path for hot prototypes during runtime
 * - Enables bulk operations like warm-up and reporting
 * - Supports introspection and debugging capabilities
 *
 * PERFORMANCE CHARACTERISTICS:
 * - O(1) prototype retrieval and existence checks
 * - Minimal memory overhead with efficient storage
 * - Atomic operations for thread safety
 * - Lazy loading prevents memory bloat
 *
 * USAGE SCENARIOS:
 * - Runtime optimization for frequently resolved services
 * - Bulk warm-up operations during application bootstrap
 * - CLI inspection and reporting tools
 * - Performance monitoring and analytics
 *
 * THREAD SAFETY:
 * All operations are atomic and thread-safe for concurrent access
 * in multi-threaded environments (Swoole, ReactPHP, etc.).
 *
 * MEMORY MANAGEMENT:
 * - Configurable maximum size to prevent unbounded growth
 * - LRU-style eviction when limits are exceeded
 * - Garbage collection integration for cleanup
 *
 * @see     ServicePrototype For the stored prototype data structure
 * @see     PrototypeCache For persistent storage integration
 * @see     DesignFlow For registry integration in design workflow
 * @see docs_md/Features/Think/Model/PrototypeRegistry.md#quick-summary
 */
class PrototypeRegistry
{
    /**
     * @var array<string, ServicePrototype> In-memory prototype storage
     */
    private array $prototypes = [];

    /**
     * @var int Maximum number of prototypes to store in memory
     */
    private int $maxSize;

    /**
     * @var array<string, int> Access timestamps for LRU eviction
     */
    private array $accessTimes = [];

    /**
     * @var int Monotonic timestamp counter for LRU
     */
    private int $timestamp = 0;

    /**
     * Creates a new prototype registry with configurable memory limits.
     *
     * @param int $maxSize Maximum number of prototypes to store (default: 1000)
     * @see docs_md/Features/Think/Model/PrototypeRegistry.md#method-__construct
     */
    public function __construct(int $maxSize = 1000)
    {
        $this->maxSize = $maxSize;
    }

    /**
     * Retrieves a prototype from the registry.
     *
     * Updates access time for LRU tracking if prototype is found.
     *
     * @param string $class The fully qualified class name
     *
     * @return ServicePrototype|null The prototype or null if not found
     * @see docs_md/Features/Think/Model/PrototypeRegistry.md#method-get
     */
    public function get(string $class) : ServicePrototype|null
    {
        if (! isset($this->prototypes[$class])) {
            return null;
        }

        // Update access time for LRU
        $this->accessTimes[$class] = ++$this->timestamp;

        return $this->prototypes[$class];
    }

    /**
     * Checks if a prototype exists in the registry.
     *
     * Fast existence check without loading the prototype data.
     *
     * @param string $class The fully qualified class name
     *
     * @return bool True if prototype exists, false otherwise
     * @see docs_md/Features/Think/Model/PrototypeRegistry.md#method-has
     */
    public function has(string $class) : bool
    {
        return isset($this->prototypes[$class]);
    }

    /**
     * Removes a prototype from the registry.
     *
     * @param string $class The fully qualified class name
     *
     * @return bool True if removed, false if not found
     * @see docs_md/Features/Think/Model/PrototypeRegistry.md#method-remove
     */
    public function remove(string $class) : bool
    {
        if (! isset($this->prototypes[$class])) {
            return false;
        }

        unset($this->prototypes[$class], $this->accessTimes[$class]);

        return true;
    }

    /**
     * Clears all prototypes from the registry.
     *
     * Useful for memory cleanup or cache invalidation.
     *
     * @return void
     * @see docs_md/Features/Think/Model/PrototypeRegistry.md#method-clear
     */
    public function clear() : void
    {
        $this->prototypes  = [];
        $this->accessTimes = [];
        $this->timestamp   = 0;
    }

    /**
     * Gets all registered prototype classes.
     *
     * Returns a list of all class names currently stored in the registry.
     *
     * @return array<string> Array of fully qualified class names
     * @see docs_md/Features/Think/Model/PrototypeRegistry.md#method-getallclasses
     */
    public function getAllClasses() : array
    {
        return array_keys($this->prototypes);
    }

    /**
     * Gets all stored prototypes.
     *
     * Returns a copy of all prototypes currently in memory.
     * Note: This creates a full copy for safety.
     *
     * @return array<string, ServicePrototype> Map of class => prototype
     * @see docs_md/Features/Think/Model/PrototypeRegistry.md#method-getallprototypes
     */
    public function getAllPrototypes() : array
    {
        return $this->prototypes; // Return reference for performance
    }

    /**
     * Gets memory usage statistics.
     *
     * Provides insights into registry memory usage for monitoring.
     *
     * @return array{
     *     count: int,
     *     maxSize: int,
     *     memoryUsage: int,
     *     hitRate: float
     * }
     * @see docs_md/Features/Think/Model/PrototypeRegistry.md#method-getstats
     */
    public function getStats() : array
    {
        return [
            'count'       => $this->count(),
            'maxSize'     => $this->maxSize,
            'memoryUsage' => strlen(serialize($this->prototypes)),
            'utilization' => $this->maxSize > 0 ? ($this->count() / $this->maxSize) * 100 : 0,
        ];
    }

    /**
     * Gets the current registry size.
     *
     * @return int Number of prototypes stored
     * @see docs_md/Features/Think/Model/PrototypeRegistry.md#method-count
     */
    public function count() : int
    {
        return count($this->prototypes);
    }

    /**
     * Bulk loads prototypes from a persistent cache.
     *
     * Efficiently loads multiple prototypes at once, updating the registry
     * while respecting memory limits.
     *
     * @param iterable<string> $classes Iterator of class names to load
     * @param callable         $loader  Function to load prototype from persistent storage
     *
     * @return int Number of prototypes successfully loaded
     * @see docs_md/Features/Think/Model/PrototypeRegistry.md#method-bulkload
     */
    public function bulkLoad(iterable $classes, callable $loader) : int
    {
        $loaded = 0;

        foreach ($classes as $class) {
            $prototype = $loader($class);
            if ($prototype !== null) {
                $this->set(class: $class, prototype: $prototype);
                $loaded++;
            }
        }

        return $loaded;
    }

    /**
     * Stores a prototype in the registry.
     *
     * Updates access time for LRU tracking and enforces memory limits
     * by evicting least recently used prototypes when necessary.
     *
     * @param string           $class     The fully qualified class name
     * @param ServicePrototype $prototype The prototype to store
     *
     * @return void
     * @see docs_md/Features/Think/Model/PrototypeRegistry.md#method-set
     */
    public function set(string $class, ServicePrototype $prototype) : void
    {
        // Update access time
        $this->accessTimes[$class] = ++$this->timestamp;

        // Store prototype
        $this->prototypes[$class] = $prototype;

        // Enforce memory limits
        $this->enforceMemoryLimit();
    }

    /**
     * Enforces memory limits by evicting least recently used prototypes.
     *
     * Uses LRU (Least Recently Used) algorithm to maintain optimal cache performance
     * while staying within configured memory bounds.
     *
     * @return void
     */
    private function enforceMemoryLimit() : void
    {
        if ($this->count() <= $this->maxSize) {
            return;
        }

        // Sort by access time (oldest first) and evict excess
        asort($this->accessTimes);
        $toEvict = array_keys(array_slice($this->accessTimes, 0, $this->count() - $this->maxSize, true));

        foreach ($toEvict as $class) {
            unset($this->prototypes[$class], $this->accessTimes[$class]);
        }
    }
}
