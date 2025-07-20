<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Contracts;

/**
 * Interface SessionBagInterface
 *
 * Represents an isolated namespace or "bag" within a session.
 * Each bag is responsible for its own data lifecycle, scoping, and operations.
 *
 * Inspired by Symfony and Laravel flash/input systems.
 */
interface SessionBagInterface
{
    /**
     * Retrieves a value by key from the bag.
     *
     * @param string     $key     The key name.
     * @param mixed|null $default The default value if the key does not exist.
     *
     * @return mixed|null The value or default.
     */
    public function get(string $key, mixed $default = null) : mixed;

    /**
     * Stores a value in the bag under the given key.
     *
     * @param string $key   The key name.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function put(string $key, mixed $value) : void;

    /**
     * Determines whether the bag contains the specified key.
     *
     * @param string $key The key name.
     *
     * @return bool True if the key exists.
     */
    public function has(string $key) : bool;

    /**
     * Retrieves all key-value pairs from the bag.
     *
     * @return array<string, mixed> All stored items.
     */
    public function all() : array;

    /**
     * Removes the specified key from the bag.
     *
     * @param string $key The key to remove.
     *
     * @return void
     */
    public function forget(string $key) : void;

    /**
     * Clears all values from the bag.
     *
     * @return void
     */
    public function clear() : void;
}
