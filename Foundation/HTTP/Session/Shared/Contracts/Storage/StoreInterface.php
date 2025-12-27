<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Contracts\Storage;

/**
 * StoreInterface - Storage Abstraction
 *
 * Minimal contract for session persistence.
 *
 * Implementations:
 * - FileStore: File-based storage
 * - NativeStore: PHP native sessions
 * - ArrayStore: In-memory (for testing)
 * - RedisStore: Redis backend
 * - DatabaseStore: Database backend
 *
 * @package Avax\HTTP\Session
 */
interface StoreInterface
{
    /**
     * Retrieve a value.
     *
     * @param string $key     The key.
     * @param mixed  $default Default value.
     *
     * @return mixed The value or default.
     */
    public function get(string $key, mixed $default = null) : mixed;

    /**
     * Store a value with optional TTL.
     *
     * @param string   $key   The key.
     * @param mixed    $value The value.
     * @param int|null $ttl   Time-to-live in seconds (null = never expires).
     *
     * @return void
     */
    public function put(string $key, mixed $value, int|null $ttl = null) : void;

    /**
     * Check if key exists.
     *
     * @param string $key The key.
     *
     * @return bool True if exists.
     */
    public function has(string $key) : bool;

    /**
     * Delete a value.
     *
     * @param string $key The key.
     *
     * @return void
     */
    public function delete(string $key) : void;

    /**
     * Get all data.
     *
     * @return array<string, mixed> All data.
     */
    public function all() : array;

    /**
     * Clear all data.
     *
     * @return void
     */
    public function flush() : void;

    /**
     * Flush all keys matching a namespace prefix.
     *
     * Example: flushNamespace('cart') deletes 'cart.items', 'cart.total', etc.
     *
     * @param string $prefix The namespace prefix.
     *
     * @return void
     */
    public function flushNamespace(string $prefix) : void;
}
