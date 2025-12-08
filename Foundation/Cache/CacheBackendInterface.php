<?php

declare(strict_types=1);

namespace Avax\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Interface CacheBackendInterface
 *
 * Defines a contract for a cache backend. Implements PSR-16 for compatibility.
 */
interface CacheBackendInterface extends CacheInterface
{
    /**
     * Clears a specific namespace or group of cache items if supported.
     *
     * @param string $namespace The namespace or group to clear.
     *
     * @return bool True on success, false otherwise.
     */
    public function clearNamespace(string $namespace) : bool;

    /**
     * Increments a stored integer value atomically.
     *
     * @param string $key   Cache key.
     * @param int    $value The amount to increment by.
     *
     * @return int New incremented value.
     */
    public function increment(string $key, int $value = 1) : int;

    /**
     * Decrements a stored integer value atomically.
     *
     * @param string $key   Cache key.
     * @param int    $value The amount to decrement by.
     *
     * @return int New decremented value.
     */
    public function decrement(string $key, int $value = 1) : int;
}
