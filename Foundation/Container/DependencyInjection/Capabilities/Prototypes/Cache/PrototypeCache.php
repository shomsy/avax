<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Prototypes\Cache;

use Avax\Container\DependencyInjection\Capabilities\Prototypes\Model\ServicePrototype;

/**
 * The persistent memory interface for service blueprints.
 *
 * PrototypeCache defines the contract for storing and retrieving
 * {@see ServicePrototype} objects across multiple requests. By caching the
 * results of the Analysis phase (which is computationally expensive), the
 * container can achieve production-level performance even with complex
 * dependency graphs.
 *
 * @see     ServicePrototype The object being cached.
 * @see     FilePrototypeCache The primary production implementation.
 */
interface PrototypeCache
{
    /**
     * Retrieve a blueprint from the persistent storage.
     *
     * @param string $class The fully qualified class name.
     *
     * @return ServicePrototype|null The blueprint if it exists and is valid.
     *
     */
    public function get(string $class) : ServicePrototype|null;

    /**
     * Persist a blueprint to the storage.
     *
     * @param string           $class     The class name ID.
     * @param ServicePrototype $prototype The blueprint to save.
     *
     */
    public function set(string $class, ServicePrototype $prototype) : void;

    /**
     * Determine if a blueprint is available in the storage.
     *
     * @param string $class Class name to check.
     *
     */
    public function has(string $class) : bool;

    /**
     * Erase a specific blueprint from the storage.
     *
     * @param string $class Class name to remove.
     *
     * @return bool True if a record was actually deleted.
     *
     */
    public function delete(string $class) : bool;

    /**
     * Completely wipe the cache storage.
     *
     */
    public function clear() : void;

    /**
     * Return the total count of blueprints currently in storage.
     *
     */
    public function count() : int;

    /**
     * An optimized existence check that avoids loading or deserializing data.
     *
     * @param string $class Class name to check.
     *
     */
    public function prototypeExists(string $class) : bool;
}
