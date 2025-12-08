<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts\Storage;

/**
 * Store Interface - Storage Abstraction
 *
 * Minimal contract for session persistence.
 *
 * Implementations:
 * - NativeStore: PHP native sessions
 * - ArrayStore: In-memory (for testing)
 * - RedisStore: Redis backend
 * - DatabaseStore: Database backend
 *
 * @package Avax\HTTP\Session
 */
interface Store
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
     * Store a value.
     *
     * @param string $key   The key.
     * @param mixed  $value The value.
     *
     * @return void
     */
    public function put(string $key, mixed $value) : void;

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
}
