<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Cache;

use Avax\Container\Features\Think\Model\ServicePrototype;

/**
 * A "No-Op" cache implementation for environments where persistence is disabled.
 *
 * NullPrototypeCache is used when no cache directory is configured (e.g., in 
 * testing or development environments where fresh analysis is preferred). 
 * It implements the {@see PrototypeCache} contract but performs no 
 * operations and always returns null/empty results.
 *
 * @package Avax\Container\Features\Think\Cache
 * @see PrototypeCache
 */
final readonly class NullPrototypeCache implements PrototypeCache
{
    /**
     * Always returns null as nothing is ever stored.
     */
    public function get(string $class): ServicePrototype|null
    {
        return null;
    }

    /**
     * Performs no operation.
     */
    public function set(string $class, ServicePrototype $prototype): void
    {
        // No-op
    }

    /**
     * Always returns false.
     */
    public function has(string $class): bool
    {
        return false;
    }

    /**
     * Performs no operation.
     */
    public function delete(string $class): bool
    {
        return false;
    }

    /**
     * Performs no operation.
     */
    public function clear(): void
    {
        // No-op
    }

    /**
     * Always returns 0.
     */
    public function count(): int
    {
        return 0;
    }

    /**
     * Always returns false.
     */
    public function prototypeExists(string $class): bool
    {
        return false;
    }
}
