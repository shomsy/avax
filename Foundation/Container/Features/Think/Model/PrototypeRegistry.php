<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Model;

use Avax\Container\Features\Think\Cache\PrototypeCache;

/**
 * The high-speed in-memory vault for active class blueprints.
 *
 * The PrototypeRegistry serves as the L1 (Level 1) cache for
 * {@see ServicePrototype} objects. It provides O(1) lightning-fast access
 * to blueprints that have already been created or loaded during the current
 * process. To prevent memory leakage in long-running processes (like Swoole
 * or Octane), it implements a strict LRU (Least Recently Used) eviction
 * policy.
 *
 * @see     docs/Features/Think/Model/PrototypeRegistry.md
 * @see     ServicePrototype For the data structure being stored.
 * @see     PrototypeCache For the persistent L2 cache that backs this registry.
 */
class PrototypeRegistry
{
    /** @var array<string, ServicePrototype> Internal storage for blueprints. */
    private array $prototypes = [];

    /** @var int The maximum allowed number of blueprints in memory. */
    private int $maxSize;

    /** @var array<string, int> Tracking of access timestamps for eviction logic. */
    private array $accessTimes = [];

    /** @var int Monotonic counter to simulate high-precision timestamps. */
    private int $timestamp = 0;

    /**
     * Initializes the registry with a memory safety limit.
     *
     * @param int $maxSize Maximum blueprints to keep in RAM. Defaults to 1000.
     */
    public function __construct(int $maxSize = 1000)
    {
        $this->maxSize = $maxSize;
    }

    /**
     * Retrieve a blueprint from RAM.
     *
     * @param string $class Fully qualified class name.
     *
     * @return ServicePrototype|null The blueprint, or null if not in memory.
     *
     * @see docs/Features/Think/Model/PrototypeRegistry.md#method-get
     */
    public function get(string $class) : ServicePrototype|null
    {
        if (! isset($this->prototypes[$class])) {
            return null;
        }

        // Update "Heat" level for this item (LRU)
        $this->accessTimes[$class] = ++$this->timestamp;

        return $this->prototypes[$class];
    }

    /**
     * Determine if a blueprint is currently residing in memory.
     *
     * @param string $class Class name to check.
     *
     * @see docs/Features/Think/Model/PrototypeRegistry.md#method-has
     */
    public function has(string $class) : bool
    {
        return isset($this->prototypes[$class]);
    }

    /**
     * Evict a specific blueprint from memory.
     *
     * @param string $class Class name to remove.
     *
     * @return bool True if an item was actually removed.
     *
     * @see docs/Features/Think/Model/PrototypeRegistry.md#method-remove
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
     * Purge all blueprints from memory.
     *
     * @see docs/Features/Think/Model/PrototypeRegistry.md#method-clear
     */
    public function clear() : void
    {
        $this->prototypes  = [];
        $this->accessTimes = [];
        $this->timestamp   = 0;
    }

    /**
     * List all class names currently held in memory.
     *
     * @return array<int, string>
     */
    public function getAllClasses() : array
    {
        return array_keys($this->prototypes);
    }

    /**
     * Retrieve the entire internal map of blueprints.
     *
     * @return array<string, ServicePrototype>
     */
    public function getAllPrototypes() : array
    {
        return $this->prototypes;
    }

    /**
     * Retrieve performance and usage metrics for the registry.
     *
     * @return array{count: int, maxSize: int, utilization: float}
     *
     * @see docs/Features/Think/Model/PrototypeRegistry.md#method-getstats
     */
    public function getStats() : array
    {
        return [
            'count'       => $this->count(),
            'maxSize'     => $this->maxSize,
            'utilization' => $this->maxSize > 0 ? ($this->count() / $this->maxSize) * 100 : 0.0,
        ];
    }

    /**
     * Return the total count of shortcuts stored in memory.
     */
    public function count() : int
    {
        return count($this->prototypes);
    }

    /**
     * Perform a bulk-load of blueprints into the registry using a loader callback.
     *
     * @param iterable<string> $classes List of classes to load.
     * @param callable         $loader  A function(string) that returns ?ServicePrototype.
     *
     * @return int The total number of prototypes successfully brought into RAM.
     *
     * @see docs/Features/Think/Model/PrototypeRegistry.md#method-bulkload
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
     * Store a blueprint in memory, potentially triggering eviction of old items.
     *
     * @param string           $class     The class name ID.
     * @param ServicePrototype $prototype The blueprint to store.
     *
     * @see docs/Features/Think/Model/PrototypeRegistry.md#method-set
     */
    public function set(string $class, ServicePrototype $prototype) : void
    {
        $this->accessTimes[$class] = ++$this->timestamp;
        $this->prototypes[$class]  = $prototype;

        $this->enforceMemoryLimit();
    }

    /**
     * Internal logic for removing least-recently-used items when the limit is reached.
     */
    private function enforceMemoryLimit() : void
    {
        if ($this->count() <= $this->maxSize) {
            return;
        }

        // Sort by access time (oldest first)
        asort($this->accessTimes);

        // Calculate number of items to evict
        $excessCount = $this->count() - $this->maxSize;
        $toEvict     = array_keys(array_slice($this->accessTimes, 0, $excessCount, true));

        foreach ($toEvict as $class) {
            unset($this->prototypes[$class], $this->accessTimes[$class]);
        }
    }
}
