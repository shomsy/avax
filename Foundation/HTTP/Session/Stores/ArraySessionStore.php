<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Stores;

use Avax\HTTP\Session\Contracts\SessionStoreInterface;
use RuntimeException;

/**
 * Class ArraySessionStore
 *
 * This is an in-memory implementation of the `SessionStoreInterface` for handling session data.
 * The store does **not persist state** between requests, making it ideal for testing or use in stateless environments.
 * Implements core session store operations such as retrieving, storing, and deleting session data.
 * The store is simple and does not deal with session IDs or persistence layers.
 */
final class ArraySessionStore implements SessionStoreInterface
{
    /**
     * @var array<string, mixed> $data
     *
     * The underlying in-memory array used to store session key-value pairs.
     * Keys are strings, and values can be of any type.
     */
    private array $data = [];

    /**
     * @var bool $started
     *
     * Indicates whether the session has started.
     * A session must be "started" before data can be read from or written to it.
     * This ensures consistency in session operations.
     */
    private bool $started = false;

    /**
     * Retrieve a session value using its key.
     *
     * Looks for the given `$key` in the internal session storage.
     * If the key is not found, the `$default` value is returned.
     * Automatically ensures the session has started before accessing the data.
     *
     * @param string     $key     The session key to retrieve.
     * @param mixed|null $default A fallback value to return if `$key` does not exist (defaults to null).
     *
     * @return mixed The value associated with `$key`, or `$default` if the key does not exist.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        // Ensure the session has been started before accessing data.


        // Return the value from session data or the default if not found.
        return $this->data[$key] ?? $default;
    }

    /**
     * Remove a session key.
     *
     * Deletes a specific `$key` from the session storage.
     * If the `$key` does not exist in the session, this method does nothing.
     * Automatically ensures the session is started before modifying data.
     *
     * @param string $key The session key to delete.
     *
     * @return void
     */
    public function delete(string $key) : void
    {
        // Ensure the session is started before attempting to delete a key.


        // Remove the specified key from the session data.
        unset($this->data[$key]);
    }

    /**
     * Retrieve all session data.
     *
     * Returns the entire session storage array as key-value pairs.
     * Automatically ensures the session is started before reading data.
     *
     * @return array<string, mixed> The entire session dataset.
     */
    public function all() : array
    {
        // Ensure the session is started before retrieving all data.


        // Return the full in-memory session store.
        return $this->data;
    }

    /**
     * Regenerate the session ID.
     *
     * This operation is typically used to mitigate session fixation attacks in persistent stores.
     * For this in-memory implementation, regenerating the session ID has no real effect,
     * but optionally clears session data when `$deleteOldSession` is true.
     *
     * @param bool $deleteOldSession Whether to delete all old session data (defaults to true).
     *
     * @return void
     */
    public function regenerateId(bool $deleteOldSession = true) : void
    {
        // Ensure the session is started before attempting to regenerate the ID.


        // If the old session data is to be cleared, flush all session data.
        if ($deleteOldSession) {
            $this->flush();
        }
        // No actual session ID logic is implemented for an in-memory store.
    }

    /**
     * Flush all session data.
     *
     * Completely purges all key-value pairs stored in the session.
     * This operation **permanently removes all stored data within the session's lifecycle.**
     * Automatically ensures the session is started before clearing data.
     *
     * @return void
     */
    public function flush() : void
    {
        // Ensure the session is started before flushing its data.


        // Empty the entire session store.
        $this->data = [];
    }

    /**
     * Stores a value in the session with a Time-To-Live (TTL).
     *
     * This method adds two entries into the session storage:
     * - The primary key with the associated value.
     * - A metadata key (`{$key}::__meta`) that tracks the expiry of the primary key.
     *
     * The TTL is used to calculate an expiration timestamp, which can be referenced
     * later to determine if the session value has expired.
     *
     * Usage of metadata allows separation of session value and its lifecycle management
     * information, keeping the session data structure clean and scalable.
     *
     * @param string $key   The unique key under which the value will be stored.
     *                      Ensures proper scoping and organization of session keys.
     * @param mixed  $value The value to store in the session.
     *                      This can be any serializable PHP data type.
     * @param int    $ttl   The time-to-live in seconds for the session entry.
     *                      Represents the lifespan of the session value from the time of storage.
     *
     * @throws RuntimeException If the session is not started or fails to write.
     *                          Ensures robust error handling in a session-based context.
     */
    public function putWithTTL(string $key, mixed $value, int $ttl) : void
    {
        // Store the main value in the session under the specified key.
        // This key represents the user's data to be tracked.
        $this->put(
            key  : $key,
            value: $value
        );

        // Store metadata about the session value to track its expiration.
        // The metadata includes an `expires_at` timestamp to record the TTL.
        $this->put(
            key  : "{$key}::__meta",
            value: ['expires_at' => time() + $ttl]
        );
    }

    /**
     * Save a value in the session by key.
     *
     * Adds or updates the given `$key` with the new `$value` in the internal session storage array.
     * Automatically ensures the session is started before writing data.
     *
     * @param string $key   The key under which to store the value.
     * @param mixed  $value The value to store (can be of any type).
     *
     * @return void
     */
    public function put(string $key, mixed $value) : void
    {
        // Ensure the session is started before writing data.


        // Store the key-value pair in the in-memory session data.
        $this->data[$key] = $value;
    }

    /**
     * Ensure the session is initialized and started.
     *
     * Automatically starts the session if it has not already been started.
     * This method guarantees that session operations are only executed after the session has been initialized.
     *
     * @return void
     */
    private function ensureSessionStarted() : void
    {
        // Start the session if it has not yet been marked as started.
        if (! $this->started) {
            $this->start();
        }
    }

    /**
     * Start the session.
     *
     * Marks the session as "started", allowing read or write operations to proceed.
     * If the session is already marked as `started`, this method does nothing.
     * Calling this method multiple times is safe.
     *
     * @return void
     */
    public function start() : void
    {
        // Check the session state and set as started if needed.
        if (! $this->started) {
            $this->started = true;
        }
    }

}