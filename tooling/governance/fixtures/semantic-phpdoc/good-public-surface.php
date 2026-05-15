<?php

/**
 * PublicSurface facade for cache operations.
 *
 * Receives public cache read/write/remember calls and delegates to internal
 * flows and capabilities. Must not own cache storage logic or runtime assembly.
 */
final readonly class GoodPublicSurface
{
    /**
     * Retrieves a value from cache by key.
     *
     * Returns null when the key does not exist or is expired.
     *
     * @param non-empty-string $key The cache key.
     *
     * @return mixed The cached value or null.
     */
    public function get(string $key): mixed
    {
        return null;
    }

    /**
     * Stores a value in cache with TTL.
     *
     * @param non-empty-string $key   The cache key.
     * @param mixed            $value The value to store.
     * @param int              $ttl   Time-to-live in seconds.
     */
    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
    }
}
