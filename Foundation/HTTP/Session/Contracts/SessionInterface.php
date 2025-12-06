<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts;

use ArrayAccess;
use Closure;

/**
 * Interface SessionInterface
 *
 * Defines the contract for managing session storage with advanced capabilities such as encryption,
 * time-to-live (TTL) expiration, and full control over the session lifecycle. This interface
 * ensures implementation flexibility by remaining Foundation-agnostic and highly portable.
 *
 * Common Use Cases:
 * - Storing user-specific data securely during a session.
 * - Managing temporary application state without coupling to a specific Foundation.
 * - Providing strong adherence to SOLID and DDD principles in session management architecture.
 *
 * Expectations:
 * - Encryption of session data for enhanced security.
 * - Configurable data expiration using TTL.
 * - Full lifecycle control (starting, invalidating, regenerating sessions).
 *
 * This interface inherits `ArrayAccess` to allow session storage manipulation via array-like syntax.
 *
 * @package Avax\HTTP\Session\Contracts
 */
interface SessionInterface extends ArrayAccess
{
    /**
     * Starts the session lifecycle.
     *
     * This method should initialize the session, preparing it to store and retrieve session data.
     * If a session is already active, implementations should avoid reinitialization.
     *
     * @return void
     */
    public function start() : void;

    /**
     * Flushes all session data.
     *
     * Performs a complete reset by clearing all stored session data. Useful when the session should
     * be completely emptied, such as during logout procedures.
     *
     * @return void
     */
    public function flush() : void;

    /**
     * Deletes a specific key from the session storage.
     *
     * @param string $key The key identifying the session entry to be deleted.
     *
     * @return void
     */
    public function delete(string $key) : void;

    /**
     * Retrieves a value from the session storage.
     *
     * @param string     $key     The key for the session entry.
     * @param mixed|null $default The default value to return if the key does not exist.
     *
     * @return mixed The stored value, or the provided default.
     */
    public function get(string $key, mixed $default = null) : mixed;

    /**
     * Stores a value in the session storage.
     *
     * @param string $key   The key for the session entry.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function set(string $key, mixed $value) : void;

    /**
     * An alias to `set`. Ensures consistency in naming styles.
     *
     * @param string $key   The key for the session entry.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function put(string $key, mixed $value) : void;

    /**
     * Stores a value in the session with a defined time-to-live (TTL).
     *
     * Data stored using this method will expire and be invalidated after the specified TTL.
     *
     * @param string $key   The key for the session entry.
     * @param mixed  $value The value to store.
     * @param int    $ttl   Time-to-live in seconds for the session entry.
     *
     * @return void
     */
    public function putWithTTL(string $key, mixed $value, int $ttl) : void;

    /**
     * Checks whether the session contains a specific key.
     *
     * @param string $key The key to check for existence in the session.
     *
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key) : bool;

    /**
     * Retrieves all key-value pairs stored in the session.
     *
     * @return array<string, mixed> An associative array of all session entries.
     */
    public function all() : array;

    /**
     * Retrieves and removes a value from the session.
     *
     * Useful for cases where session data is meant to be consumed only once.
     *
     * @param string     $key     The key for the session entry.
     * @param mixed|null $default The default value if the key does not exist.
     *
     * @return mixed The value, or the default if not found.
     */
    public function pull(string $key, mixed $default = null) : mixed;

    /**
     * Retrieves a value from the session or executes a callback to generate it.
     *
     * If the key does not exist, the callback will generate and store the value, ensuring lazy evaluation.
     *
     * @param string  $key      The key for the session entry.
     * @param Closure $callback A callback returning the value to store if the key does not exist.
     *
     * @return mixed The retrieved or newly generated value.
     */
    public function remember(string $key, Closure $callback) : mixed;

    /**
     * Increments the value of a specific session entry.
     *
     * @param string $key    The key for the session entry.
     * @param int    $amount The amount to increment by (default: 1).
     *
     * @return int The incremented value.
     */
    public function increment(string $key, int $amount = 1) : int;

    /**
     * Decrements the value of a specific session entry.
     *
     * @param string $key    The key for the session entry.
     * @param int    $amount The amount to decrement by (default: 1).
     *
     * @return int The decremented value.
     */
    public function decrement(string $key, int $amount = 1) : int;

    /**
     * Regenerates the session ID.
     *
     * This is useful for preventing session fixation attacks. Optionally, the old session data can be deleted.
     *
     * @param bool $deleteOldSession Whether to delete the old session (default: true).
     *
     * @return void
     */
    public function regenerateId(bool $deleteOldSession = true) : void;

    /**
     * Invalidates the current session.
     *
     * Typically used for logout or resetting session state.
     *
     * @return void
     */
    public function invalidate() : void;

    /**
     * Retrieves data from the previous request.
     *
     * This is commonly used to repopulate old form inputs after redirects.
     *
     * @param string     $key     The key for the session entry.
     * @param mixed|null $default The default value if the key does not exist.
     *
     * @return mixed The retrieved old input, or the provided default.
     */
    public function getOldInput(string $key, mixed $default = null) : mixed;

    public function getRegistry() : BagRegistryInterface;

}