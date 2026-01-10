<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Cache;

use Avax\Container\Features\Think\Model\ServicePrototype;

/**
 * The persistent memory interface for service blueprints.
 *
 * PrototypeCache defines the contract for storing and retrieving 
 * {@see ServicePrototype} objects across multiple requests. By caching the 
 * results of the Analysis phase (which is computationally expensive), the 
 * container can achieve production-level performance even with complex 
 * dependency graphs.
 *
 * @package Avax\Container\Features\Think\Cache
 * @see docs/Features/Think/Cache/PrototypeCache.md
 * @see ServicePrototype The object being cached.
 * @see FilePrototypeCache The primary production implementation.
 */
interface PrototypeCache
{
    /**
     * Retrieve a blueprint from the persistent storage.
     *
     * @param string $class The fully qualified class name.
     * @return ServicePrototype|null The blueprint if it exists and is valid.
     *
     * @see docs/Features/Think/Cache/PrototypeCache.md#method-get
     */
    public function get(string $class): ServicePrototype|null;

    /**
     * Persist a blueprint to the storage.
     *
     * @param string           $class     The class name ID.
     * @param ServicePrototype $prototype The blueprint to save.
     * @return void
     *
     * @see docs/Features/Think/Cache/PrototypeCache.md#method-set
     */
    public function set(string $class, ServicePrototype $prototype): void;

    /**
     * Determine if a blueprint is available in the storage.
     *
     * @param string $class Class name to check.
     * @return bool
     * @see docs/Features/Think/Cache/PrototypeCache.md#method-has
     */
    public function has(string $class): bool;

    /**
     * Erase a specific blueprint from the storage.
     *
     * @param string $class Class name to remove.
     * @return bool True if a record was actually deleted.
     *
     * @see docs/Features/Think/Cache/PrototypeCache.md#method-delete
     */
    public function delete(string $class): bool;

    /**
     * Completely wipe the cache storage.
     *
     * @return void
     * @see docs/Features/Think/Cache/PrototypeCache.md#method-clear
     */
    public function clear(): void;

    /**
     * Return the total count of blueprints currently in storage.
     *
     * @return int
     * @see docs/Features/Think/Cache/PrototypeCache.md#method-count
     */
    public function count(): int;

    /**
     * An optimized existence check that avoids loading or deserializing data.
     *
     * @param string $class Class name to check.
     * @return bool
     * @see docs/Features/Think/Cache/PrototypeCache.md#method-prototypeexists
     */
    public function prototypeExists(string $class): bool;
}
