<?php

declare(strict_types=1);

namespace Avax\Cache;

use Avax\Cache\Exception\InMemoryInvalidArgumentException;
use DateInterval;
use DateTimeImmutable;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class InMemoryCache
 *
 * A lightweight in-memory caching system implementing PSR-16.
 *
 * Features:
 * - Supports expiration times (TTL)
 * - Implements atomic increment/decrement operations
 * - Provides namespace-based cache clearing
 * - Handles batch operations efficiently
 */
class InMemoryCache implements CacheInterface
{
    /**
     * @var array<string, array{value: mixed, expires_at: int|null}>
     * Holds cached items with expiration metadata.
     */
    private array $cache = [];

    /**
     * Clears all cached items.
     *
     * @return bool True on success.
     */
    public function clear() : bool
    {
        $this->cache = [];

        return true;
    }

    /**
     * Retrieves multiple items from the cache.
     *
     * @param iterable $keys    The list of cache keys.
     * @param mixed    $default Default value if key does not exist.
     *
     * @return iterable<string, mixed> The key-value pairs.
     *
     * @throws InvalidArgumentException If any key is invalid.
     */
    public function getMultiple(iterable $keys, mixed $default = null) : iterable
    {
        $this->validateKeys(keys: $keys);

        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get(key: $key, default: $default);
        }

        return $results;
    }

    /**
     * Validates multiple cache keys.
     *
     * @throws InvalidArgumentException If any key is invalid.
     */
    private function validateKeys(iterable $keys) : void
    {
        foreach ($keys as $key) {
            $this->validateKey(key: $key);
        }
    }

    /**
     * Validates a cache key.
     *
     * @throws InvalidArgumentException If the key is invalid.
     */
    private function validateKey(string $key) : void
    {
        if (! is_string(value: $key) || trim(string: $key) === '') {
            throw new InMemoryInvalidArgumentException(message: 'Cache key must be a non-empty string.');
        }
    }

    /**
     * Retrieves a value from the cache.
     *
     * @param string $key     The cache key.
     * @param mixed  $default Default value if key does not exist or is expired.
     *
     * @return mixed The cached value or default if key is not found.
     *
     * @throws InvalidArgumentException If the key is invalid.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        $this->validateKey(key: $key);

        if (! $this->has(key: $key)) {
            return $default;
        }

        return $this->cache[$key]['value'];
    }

    /**
     * Checks if a key exists in the cache and is not expired.
     *
     * @param string $key The cache key.
     *
     * @return bool True if the item exists and is valid, false otherwise.
     *
     * @throws InvalidArgumentException If the key is invalid.
     */
    public function has(string $key) : bool
    {
        $this->validateKey(key: $key);

        if (! isset($this->cache[$key])) {
            return false;
        }

        $expiresAt = $this->cache[$key]['expires_at'];
        if ($expiresAt !== null && $expiresAt < time()) {
            $this->delete(key: $key);

            return false;
        }

        return true;
    }

    /**
     * Deletes an item from the cache.
     *
     * @param string $key The cache key.
     *
     * @return bool True on success.
     *
     * @throws InvalidArgumentException If the key is invalid.
     */
    public function delete(string $key) : bool
    {
        $this->validateKey(key: $key);
        unset($this->cache[$key]);

        return true;
    }

    /**
     * Stores multiple items in the cache.
     *
     * @param iterable              $values The key-value pairs.
     * @param int|DateInterval|null $ttl    Time-to-live for all values.
     *
     * @return bool True on success.
     *
     * @throws InvalidArgumentException If any key is invalid.
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null) : bool
    {
        $this->validateKeys(keys: array_keys(array: iterator_to_array(iterator: $values)));

        foreach ($values as $key => $value) {
            $this->set(key: $key, value: $value, ttl: $ttl);
        }

        return true;
    }

    /**
     * Stores an item in the cache.
     *
     * @param string                $key   The cache key.
     * @param mixed                 $value The value to store.
     * @param int|DateInterval|null $ttl   Time-to-live in seconds or a DateInterval.
     *
     * @return bool True on success.
     *
     * @throws InvalidArgumentException If the key is invalid.
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        $this->validateKey(key: $key);

        $expiresAt         = $this->calculateExpirationTime(ttl: $ttl);
        $this->cache[$key] = ['value' => $value, 'expires_at' => $expiresAt];

        return true;
    }

    /**
     * Calculates the expiration timestamp.
     */
    private function calculateExpirationTime(int|DateInterval|null $ttl) : int|null
    {
        if ($ttl === null) {
            return null;
        }

        return ($ttl instanceof DateInterval)
            ? (new DateTimeImmutable())->add(interval: $ttl)->getTimestamp()
            : (time() + $ttl);
    }

    /**
     * Deletes multiple items from the cache.
     *
     * @param iterable $keys The list of cache keys.
     *
     * @return bool True on success.
     *
     * @throws InvalidArgumentException If any key is invalid.
     */
    public function deleteMultiple(iterable $keys) : bool
    {
        $this->validateKeys(keys: $keys);

        foreach ($keys as $key) {
            $this->delete(key: $key);
        }

        return true;
    }

    /**
     * Decrements a numeric value in the cache.
     *
     * @param string $key   The cache key.
     * @param int    $value The decrement amount.
     *
     * @return int The new decremented value.
     *
     * @throws InvalidArgumentException If the key is invalid.
     */
    public function decrement(string $key, int $value = 1) : int
    {
        return $this->increment(key: $key, value: -$value);
    }

    /**
     * Increments a numeric value in the cache.
     *
     * @param string $key   The cache key.
     * @param int    $value The increment amount.
     *
     * @return int The new incremented value.
     *
     * @throws InvalidArgumentException If the key is invalid.
     */
    public function increment(string $key, int $value = 1) : int
    {
        $this->validateKey(key: $key);

        $currentValue = $this->get(key: $key, default: 0);
        if (! is_numeric(value: $currentValue)) {
            throw new InMemoryInvalidArgumentException(message: "Value at key '$key' is not numeric.");
        }

        $newValue = (int) $currentValue + $value;
        $this->set(key: $key, value: $newValue);

        return $newValue;
    }

    /**
     * Clears all items within a specific namespace.
     *
     * @param string $namespace The namespace prefix to clear.
     *
     * @return bool True on success.
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function clearNamespace(string $namespace) : bool
    {
        foreach (array_keys(array: $this->cache) as $key) {
            if (str_starts_with(haystack: $key, needle: $namespace . ':')) {
                $this->delete(key: $key);
            }
        }

        return true;
    }
}
