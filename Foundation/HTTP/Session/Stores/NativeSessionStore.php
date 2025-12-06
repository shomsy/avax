<?php
/** @noinspection GlobalVariableUsageInspection */

/**
 * NativeSessionStore
 *
 * This class provides an implementation of the SessionStoreInterface using
 * native PHP's session mechanism ($_SESSION). It focuses on encapsulating
 * session handling to ensure clean and reusable code for managing session
 * states and data.
 *
 * @package Avax\HTTP\Session\Stores
 */

declare(strict_types=1);

namespace Avax\HTTP\Session\Stores;

use Avax\HTTP\Session\Contracts\SessionStoreInterface;
use RuntimeException;

/**
 * Final class implementing the SessionStoreInterface.
 * Encapsulates native PHP session logic to manage session data securely and efficiently.
 */
final class NativeSessionStore implements SessionStoreInterface
{
    /**
     * Retrieve a session value by its key.
     *
     * If the session key does not exist, the default value is returned instead.
     *
     * @param string     $key     The unique key in the session to retrieve the value for.
     * @param mixed|null $default A fallback value if the key is not found (default: null).
     *
     * @return mixed The value associated with the key, or the default value if not found.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        // Ensure the session is started before accessing $_SESSION.
        $this->start();

        // Retrieve the value from the session storage or return the default.
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Start the session if it hasn't already been started.
     *
     * This ensures that session-related operations can safely proceed.
     *
     * @return void
     */
    public function start() : void
    {
        // Check if the session is not active and initialize it.
        if (session_status() === PHP_SESSION_NONE) {
            // Start the native PHP session.
            session_start();
        }
    }

    /**
     * Remove a value from the session by its key.
     *
     * If the key does not exist, this operation has no effect.
     *
     * @param string $key The unique key of the session value to delete.
     *
     * @return void
     */
    public function delete(string $key) : void
    {
        // Ensure the session is started before attempting to delete the key.
        $this->start();

        // Remove the specified key from the session storage.
        unset($_SESSION[$key]);
    }

    /**
     * Retrieve all session data as a key-value associative array.
     *
     * @return array<string, mixed> The entire session data.
     */
    public function all() : array
    {
        // Ensure the session is started before accessing $_SESSION.
        $this->start();

        // Return the entire $_SESSION data.
        return $_SESSION;
    }

    /**
     * Clear all session data.
     *
     * WARNING: This will remove all session key-value pairs.
     * Use with caution as this action is irreversible for the scope of the session.
     *
     * @return void
     */
    public function flush() : void
    {
        // Ensure the session is started before clearing all session data.
        $this->start();

        // Empty the session array, effectively removing all data.
        $_SESSION = [];
    }

    /**
     * Regenerate the session ID.
     *
     * This is useful for preventing session fixation attacks by assigning
     * a new session ID to the current session context.
     *
     * @param bool $deleteOldSession Indicates whether to delete the old session data.
     *                               Default is true to enhance security.
     *
     * @return void
     */
    public function regenerateId(bool $deleteOldSession = true) : void
    {
        // Ensure the session is started before regenerating the session ID.
        $this->start();

        // Regenerate the session ID with the option to delete the old session.
        session_regenerate_id(delete_old_session: $deleteOldSession);
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
     * Store a value in the session for a given key.
     *
     * If the key already exists, the value will be overwritten.
     *
     * @param string $key   The unique key to store the value under.
     * @param mixed  $value The value to be stored in the session.
     *
     * @return void
     */
    public function put(string $key, mixed $value) : void
    {
        // Ensure the session is started before manipulating $_SESSION.
        $this->start();

        // Store the value in the session under the specified key.
        $_SESSION[$key] = $value;
    }
}