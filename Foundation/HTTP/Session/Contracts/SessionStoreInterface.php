<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts;

/**
 * SessionStoreInterface
 *
 * Defines the contract for session storage backends.
 * Storage engines must implement these methods to adhere to
 * standardized session management behavior.
 */
interface SessionStoreInterface
{
    /**
     * Initializes the session.
     *
     * This should start the session storage mechanism,
     * creating or resuming an existing session.
     *
     * @return void
     */
    public function start() : void;

    /**
     * Retrieves a session value by its key.
     *
     * @param string     $key     The session key to retrieve.
     * @param mixed|null $default The default value to return if the key is not found.
     *
     * @return mixed The value associated with the key, or the default value if not set.
     */
    public function get(string $key, mixed $default = null) : mixed;

    /**
     * Saves a value in the session.
     *
     * @param string $key   The session key to store the value under.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function put(string $key, mixed $value) : void;

    /**
     * Removes a key-value pair from the session.
     *
     * No effect if the specified key does not exist in the session.
     *
     * @param string $key The session key to delete.
     *
     * @return void
     */
    public function delete(string $key) : void;

    /**
     * Retrieves all session data as a key-value associative array.
     *
     * @return array<string, mixed> The entire session data.
     */
    public function all() : array;

    /**
     * Clears all session data.
     *
     * WARNING: This will permanently delete all session data.
     * Use with caution.
     *
     * @return void
     */
    public function flush() : void;

    /**
     * Regenerates the session ID.
     *
     * This prevents session fixation attacks by creating a new session ID.
     *
     * @param bool $deleteOldSession If true, destroys the old session data. Default is true.
     *
     * @return void
     */
    public function regenerateId(bool $deleteOldSession = true) : void;
}