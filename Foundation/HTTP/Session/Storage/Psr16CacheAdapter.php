<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Storage;

// PSR-16 Simple Cache Interface
// Note: Install psr/simple-cache via composer for production use
// This is a fallback stub for development without the package
use DateInterval;

if (! interface_exists(\Psr\SimpleCache\CacheInterface::class)) {
    /**
     * Stub interface for PSR-16 CacheInterface.
     * Install psr/simple-cache package for production use.
     */
    interface CacheInterface
    {
        public function get(string $key, mixed $default = null) : mixed;

        public function set(string $key, mixed $value, null|int|DateInterval $ttl = null) : bool;

        public function delete(string $key) : bool;

        public function clear() : bool;

        public function getMultiple(iterable $keys, mixed $default = null) : iterable;

        public function setMultiple(iterable $values, null|int|DateInterval $ttl = null) : bool;

        public function deleteMultiple(iterable $keys) : bool;

        public function has(string $key) : bool;
    }
}

/**
 * Psr16CacheAdapter - PSR-16 Simple Cache Adapter
 *
 * Bridges session Store interface with PSR-16 Simple Cache.
 * Enables session storage in Redis, Memcached, or any PSR-16 cache.
 *
 * Benefits:
 * - Interoperability with existing cache layers
 * - High-performance session storage (Redis, Memcached)
 * - Automatic expiration support
 * - Vendor-agnostic caching
 *
 * @example With Symfony Cache
 *   $cache = new FilesystemAdapter();
 *   $store = new Psr16CacheAdapter($cache);
 *   $session = new SessionProvider($store);
 *
 * @example With Laravel Cache
 *   $cache = Cache::store('redis');
 *   $store = new Psr16CacheAdapter($cache);
 *
 * @package Avax\HTTP\Session\Storage
 */
final class Psr16CacheAdapter extends AbstractStore
{
    /**
     * Psr16CacheAdapter Constructor.
     *
     * @param object|CacheInterface $cache  PSR-16 cache instance.
     * @param string                $prefix Key prefix for namespacing (default: 'session_').
     * @param int|null              $ttl    Default TTL in seconds (null = no expiration).
     */
    public function __construct(
        private object   $cache,
        private string   $prefix = 'session_',
        private int|null $ttl = null
    ) {}

    /**
     * {@inheritdoc}
     */
    public function has(string $key) : bool
    {
        return $this->cache->has($this->prefixKey($key));
    }

    /**
     * Prefix a key with namespace.
     *
     * @param string $key Original key.
     *
     * @return string Prefixed key.
     */
    private function prefixKey(string $key) : string
    {
        return $this->prefix . $key;
    }

    /**
     * {@inheritdoc}
     */
    public function all() : array
    {
        // PSR-16 doesn't support "get all keys"
        // We maintain a meta key to track all session keys
        $keys = $this->cache->get($this->prefixKey('_keys'), []);
        $data = [];

        foreach ($keys as $key) {
            $value = $this->cache->get($this->prefixKey($key));
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->cache->get($this->prefixKey($key), $default);
    }

    /**
     * {@inheritdoc}
     */
    public function flush() : void
    {
        $keys = $this->cache->get($this->prefixKey('_keys'), []);

        foreach ($keys as $key) {
            $this->cache->delete($this->prefixKey($key));
        }

        $this->cache->delete($this->prefixKey('_keys'));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key) : void
    {
        $this->cache->delete($this->prefixKey($key));
    }

    /**
     * Override put to track keys.
     *
     * @param string $key   The key.
     * @param mixed  $value The value.
     *
     * @return void
     */
    public function putWithTracking(string $key, mixed $value) : void
    {
        // Track this key
        $keys = $this->cache->get($this->prefixKey('_keys'), []);
        if (! in_array($key, $keys, true)) {
            $keys[] = $key;
            $this->cache->set($this->prefixKey('_keys'), $keys);
        }

        // Store the value
        $this->put($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, mixed $value) : void
    {
        $this->cache->set($this->prefixKey($key), $value, $this->ttl);
    }

    /**
     * Override delete to untrack keys.
     *
     * @param string $key The key.
     *
     * @return void
     */
    public function deleteWithTracking(string $key) : void
    {
        // Untrack this key
        $keys = $this->cache->get($this->prefixKey('_keys'), []);
        $keys = array_filter($keys, fn($k) => $k !== $key);
        $this->cache->set($this->prefixKey('_keys'), array_values($keys));

        // Delete the value
        $this->delete($key);
    }

    /**
     * Set default TTL.
     *
     * @param int|null $ttl TTL in seconds.
     *
     * @return void
     */
    public function setDefaultTtl(int|null $ttl) : void
    {
        $this->ttl = $ttl;
    }

    /**
     * Get underlying PSR-16 cache instance.
     *
     * @return object Cache instance.
     */
    public function getCache() : object
    {
        return $this->cache;
    }

    /**
     * Store multiple key-value pairs with TTL.
     *
     * @param array<string, mixed> $values Key-value pairs.
     * @param int|null             $ttl    Optional TTL override.
     *
     * @return void
     */
    public function putManyWithTtl(array $values, int|null $ttl = null) : void
    {
        $prefixed = [];
        foreach ($values as $key => $value) {
            $prefixed[$this->prefixKey($key)] = $value;
        }

        $this->cache->setMultiple($prefixed, $ttl ?? $this->ttl);
    }

    /**
     * Get multiple values.
     *
     * @param array<string> $keys    Keys to retrieve.
     * @param mixed|null    $default Default value.
     *
     * @return array<string, mixed> Key-value pairs.
     */
    public function getMany(array $keys, mixed $default = null) : array
    {
        $prefixed = array_map(fn($k) => $this->prefixKey($k), $keys);
        $values   = $this->cache->getMultiple($prefixed, $default);

        // Remove prefix from keys
        $result = [];
        foreach ($values as $prefixedKey => $value) {
            $originalKey          = substr($prefixedKey, strlen($this->prefix));
            $result[$originalKey] = $value;
        }

        return $result;
    }

    /**
     * Delete multiple keys.
     *
     * @param array<string> $keys Keys to delete.
     *
     * @return void
     */
    public function deleteMany(array $keys) : void
    {
        $prefixed = array_map(fn($k) => $this->prefixKey($k), $keys);
        $this->cache->deleteMultiple($prefixed);
    }

    /**
     * Clear entire cache (including non-session keys).
     *
     * WARNING: This clears the ENTIRE cache, not just session data.
     *
     * @return void
     */
    public function clearEntireCache() : void
    {
        $this->cache->clear();
    }
}
