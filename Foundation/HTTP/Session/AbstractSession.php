<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use Closure;
use Avax\HTTP\Session\Contracts\BagRegistryInterface;
use Avax\HTTP\Session\Contracts\SessionInterface;
use Avax\HTTP\Session\Contracts\SessionStoreInterface;

/**
 * AbstractSession
 *
 * Foundation-agnostic base implementation for session management.
 *
 * This class provides secure, extensible, and developer-friendly session
 * behavior using a pluggable store and cryptography service for managing
 * encrypted sessions. It adheres to clean code principles and Domain-Driven
 * Design (DDD) practices for maximum clarity and flexibility.
 *
 * Implements:
 * - `SessionInterface` for session operations.
 * - `ArrayAccess` for array-like access to session attributes.
 *
 * Key Features:
 * - Secure storage using encryption.
 * - Flash data system for temporary session storage.
 * - Foundation-independent, making it reusable across different applications.
 */
abstract class AbstractSession implements SessionInterface
{
    /**
     * Constructor.
     *
     * Initializes the session with a store and crypto engine.
     *
     * @param SessionStoreInterface $store The backend store for session data.
     */
    public function __construct(
        protected readonly SessionStoreInterface $store,
        protected readonly BagRegistryInterface  $registry
    ) {}

    /**
     * Retrieve all session data while performing cleanup of expired items.
     *
     * This method ensures the session has been started, fetches all the stored session
     * data, and removes expired entries both from the session data as well as from
     * the associated metadata. The cleanup process ensures that session data does
     * not accumulate invalid or stale entries.
     *
     * @return array The complete session data after expired items are cleaned up.
     */
    public function all() : array
    {
        // Begin the session if it is not already started.
        $this->start();

        // Retrieve all session data from the session store.
        $all = $this->store->all();

        // Iterate through each key-value pair in the session data.
        foreach ($all as $key => $value) {
            // Skip processing if the key corresponds to metadata (indicated by '::__meta' suffix).
            if (str_ends_with($key, '::__meta')) {
                continue;
            }

            // Derive the corresponding metadata key for the current session key.
            $metaKey = "{$key}::__meta";

            // Check if the metadata exists and if the session data has expired based on `expires_at`.
            if (
                isset($all[$metaKey]['expires_at']) && // Metadata contains expiry details.
                time() >= $all[$metaKey]['expires_at'] // Current time has passed the expiry timestamp.
            ) {
                // Remove the expired session key from the session store.
                $this->delete($key);

                // Remove the related metadata key from the session store.
                $this->delete($metaKey);

                // Unset the expired session data and metadata from the `$all` array.
                unset($all[$key], $all[$metaKey]);
            }
        }

        // Return the cleaned-up session data.
        return $all;
    }

    /**
     * Starts the session using the provided session store.
     *
     * This method delegates the session initialization process to the underlying
     * store implementation, ensuring a uniform session management interface.
     *
     * @return void
     */
    public function start() : void
    {
        // Start the session by delegating to the session store's start method.
        $this->store->start();
    }

    /**
     * Deletes a specific session key and its associated value from storage.
     * Before deletion, ensures that the session is started.
     * Delegates the actual deletion logic to the store implementation.
     *
     * @param string $key The session key to delete from storage.
     *
     * @return void
     */
    public function delete(string $key) : void
    {
        // Ensure the session is started.
        $this->start();

        // Request the store to delete the specified key.
        $this->store->delete(key: $key);
    }

    /**
     * Invalidate the current session by performing the following steps:
     * 1. Clear all session data to ensure no sensitive information is retained.
     * 2. Regenerate the session ID, optionally removing the old session data for security.
     *
     * This method is a defensive mechanism against session fixation attacks.
     * By regenerating the session ID, any previously held session identifiers become unusable.
     *
     * @return void
     */
    public function invalidate() : void
    {
        // Step 1: Flush all session data, ensuring a clean state for the session.
        $this->flush();

        // Step 2: Regenerate the session ID securely using the store.
        // The "deleteOldSession" flag is explicitly passed for clarity and safety.
        $this->store->regenerateId(deleteOldSession: true);
    }

    /**
     * Clear all session data.
     *
     * This method ensures the session is started before performing
     * a flush operation to avoid operating on an inactive session.
     * All session data will be permanently erased.
     *
     * Contract:
     * - The session must be active before data can be flushed.
     * - Delegates the "flush" operation to the session store for implementation.
     *
     * WARNING: Use this with caution as it destroys all stored session data.
     *
     * @return void
     */
    public function flush() : void
    {
        // Ensure the session is started.
        // This guarantees the session is active and prevents flushing
        // data in an uninitialized or inactive session state.
        $this->start();

        // Delegate the flush operation to the session store.
        // The store is responsible for clearing all persisted session data.
        $this->store->flush();
    }

    /**
     * Regenerate the session ID for the current session.
     *
     * This method ensures the security of the session by allowing users
     * to optionally delete the old session data and replacing the current
     * session ID with a new one.
     *
     * @param bool $deleteOldSession Whether to delete the old session data
     *                               (enabled by default for enhanced security).
     */
    public function regenerateId(bool $deleteOldSession = true) : void
    {
        // Delegate the request to regenerate the session ID to the session store.
        // By using named arguments, we make the intention explicit.
        $this->store->regenerateId(deleteOldSession: $deleteOldSession);
    }

    /**
     * Pulls a value from the session storage based on the given key,
     * removes the specified key from the session, and returns the value.
     *
     * @param string     $key     The unique identifier for the session item.
     * @param mixed|null $default A default value to return if the key is not found in storage.
     *
     * @return mixed The value associated with the given key, or the default if not found.
     */
    public function pull(string $key, mixed $default = null) : mixed
    {
        // Retrieve the value corresponding to the key or return the default if unavailable.
        $value = $this->get(key: $key, default: $default);

        // Delete the key-value pair from the session to ensure it cannot be retrieved again.
        $this->delete(key: $key);

        // Return the retrieved value.
        return $value;
    }

    /**
     * This method retrieves a value from the session storage by its key.
     * It performs validation on metadata to check for expiration and
     * decrypts the value before returning it.
     * If the value doesn't exist or is expired, the provided default is returned.
     *
     * @param string     $key     The unique identifier for the session data to retrieve.
     * @param mixed|null $default A fallback value to return if the data associated with the key is not found or is
     *                            expired.
     *
     * @return mixed The value retrieved from the session storage or the default provided if unavailable.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        // Ensure the session is started before interacting with the storage.
        $this->start();

        // Attempt to retrieve the value associated with the given key from the session store.
        $value = $this->store->get(key: $key);

        // Attempt to retrieve metadata associated with the key (e.g., expiration time).
        // If no metadata exists, use an empty array as the default.
        $meta = $this->store->get(key: "{$key}::__meta", default: []);

        // Check if the 'expires_at' metadata attribute exists, and whether the current time has exceeded its value.
        if (isset($meta['expires_at']) && time() >= $meta['expires_at']) {
            // If the value is expired, delete the main key and its associated metadata from the session store.
            $this->delete(key: $key);
            $this->delete(key: "{$key}::__meta");

            // Return the default value since the stored data is no longer valid.
            return $default;
        }

        // If no value is found in the session store, or if it's null, return the default value.
        // Otherwise, decrypt the value before returning it to the caller.
        return $value ?? $default;
    }

    /**
     * Stores and returns a computed value for the given session key,
     * or retrieves the existing value if it has already been set.
     *
     * @param string  $key      The key associated with the value to remember.
     * @param Closure $callback A callback to compute the value only if it does not already exist in the session.
     *
     * @return mixed The value retrieved from storage or created by the callback.
     */
    public function remember(string $key, Closure $callback) : mixed
    {
        // Check if the key already exists in the session storage.
        if ($this->has(key: $key)) {
            // Return the preexisting value if the key exists.
            return $this->get(key: $key);
        }

        // Compute the new value by invoking the provided callback.
        $value = $callback();

        // Save the computed value under the specified session key.
        $this->set(key: $key, value: $value);

        // Return the newly created value.
        return $value;
    }

    /**
     * Checks if a specific key exists in the session store.
     *
     * The `has` method checks whether a specific key exists in the store by making use
     * of the `get` method from the session storage interface and verifying if the result is not null.
     *
     * @param string $key The identifier of the data to check for in the session store.
     *
     * @return bool Returns `true` if the key is found in the session store, otherwise `false`.
     */
    public function has(string $key) : bool
    {
        // Attempt to fetch the value associated with the key from the store.
        // Return whether the key exists by checking that the retrieved value is not `null`.
        return $this->store->get(key: $key) !== null;
    }

    /**
     * Handles session data storage by securely encrypting the provided value
     * and associating it with the specified key.
     *
     * This method ensures the session storage is properly started before
     * performing operations and leverages encryption for secure data storage.
     *
     * @param string $key   The unique identifier for the session attribute.
     *                      Should be descriptive and consistent within the domain.
     * @param mixed  $value The data to be stored in the session. Can represent
     *                      any value type supported by PHP, making it flexible
     *                      for various use cases.
     *
     * @return void
     */
    public function set(string $key, mixed $value) : void
    {
        // Start the session to ensure it’s ready for storing data.
        $this->start();

        // store it in the session storage with the provided key.
        $this->store->put(
            key  : $key,
            value: $value
        );
    }

    /**
     * Stores a given key-value pair in the session storage.
     *
     * This method delegates the responsibility of storing the data
     * ensuring the value is stored explicitly
     * as plain (unencrypted) data.
     *
     * The session must be initialized before calling this method.
     *
     * @param string $key   The unique key under which the value
     *                      will be stored in the session.
     * @param mixed  $value The value to be stored in the session.
     *                      Can be of any type (e.g. scalar, array, object).
     *
     * @return void
     */
    public function put(string $key, mixed $value) : void
    {
        // Ensure that the session is started before performing any session operations.
        $this->start();

        // Use the session store to store the provided key-value pair directly.
        $this->store->put(key: $key, value: $value);
    }

    /**
     * Increments an integer value stored in the session by a specified amount.
     * If the key does not exist, it initializes the value to 0 before incrementing.
     *
     * @param string $key    The key identifying the value to increment.
     * @param int    $amount The amount to increment by. Default is 1.
     *
     * @return int The incremented value after the operation is completed.
     */
    public function increment(string $key, int $amount = 1) : int
    {
        // Retrieve the current value as an integer, defaulting to 0 if the key does not exist.
        $current = (int) $this->get(key: $key, default: 0);

        // Add the specified amount to the current value.
        $new = $current + $amount;

        // Store the updated value back in the session.
        $this->set(key: $key, value: $new);

        // Return the incremented value.
        return $new;
    }

    /**
     * Decrements an integer value stored in the session by a specified amount.
     * If the key does not exist, it initializes the value to 0 before decrementing.
     *
     * @param string $key    The key identifying the value to decrement.
     * @param int    $amount The amount to decrement by. Default is 1.
     *
     * @return int The decremented value after the operation is completed.
     */
    public function decrement(string $key, int $amount = 1) : int
    {
        // Retrieve the current value as an integer, defaulting to 0 if the key does not exist.
        $current = (int) $this->get(key: $key, default: 0);

        // Subtract the specified amount from the current value.
        $new = $current - $amount;

        // Store the updated value back in the session.
        $this->set(key: $key, value: $new);

        // Return the decremented value.
        return $new;
    }

    /**
     * Determine if the given session key exists in the session store.
     *
     * This method is required by the ArrayAccess interface. It allows checking
     * if a session key exists using array-style syntax.
     *
     * @param string $offset The session key to check.
     *
     * @return bool Returns true if the key exists, false otherwise.
     */
    public function offsetExists(mixed $offset) : bool
    {
        // Cast the offset to string to ensure compatibility with session keys.
        // Use the `has` method to determine if the key exists in the session store.
        return $this->has(key: (string) $offset);
    }

    /**
     * Retrieve the session value associated with the given key.
     *
     * This method is required by the ArrayAccess interface. It allows session values
     * to be accessed using array-style syntax.
     *
     * @param string $offset The session key to retrieve the value for.
     *
     * @return mixed The value associated with the session key, or a default value if not set.
     */
    public function offsetGet(mixed $offset) : mixed
    {
        // Cast the offset to string and retrieve its associated value using the `get` method.
        return $this->get(key: (string) $offset);
    }

    /**
     * Store a value in the session associated with the provided key.
     *
     * This method is required by the ArrayAccess interface. It allows session values
     * to be set using array-style syntax.
     *
     * @param string $offset The session key to associate with the value.
     * @param mixed  $value  The session value to be stored.
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value) : void
    {
        // Cast the offset to string and store the associated value using the `set` method.
        $this->set(key: (string) $offset, value: $value);
    }

    /**
     * Remove a session value associated with the given key.
     *
     * This method is required by the ArrayAccess interface. It allows session values
     * to be removed using array-style syntax.
     *
     * @param string $offset The session key to remove.
     *
     * @return void
     */
    public function offsetUnset(mixed $offset) : void
    {
        // Cast the offset to string and delete the associated value using the `delete` method.
        $this->delete(key: (string) $offset);
    }

    /**
     * Retrieves an old input value from the session storage.
     *
     * This method is particularly useful for retrieving input values from
     * requests in previous forms (e.g., old form submissions). If the specified key
     * does not exist in the old input storage, a default value is returned.
     *
     * @param string $key     The unique key associated with the old input data.
     * @param mixed  $default The default value to return if the key is not found (optional).
     *
     * @return mixed The retrieved old input value or the default value.
     */
    public function getOldInput(string $key, mixed $default = null) : mixed
    {
        // Retrieve the '_old_input' array from the session store.
        $data = $this->store->get(key: '_old_input', default: []);

        // Ensure the retrieved data is an array and contains the desired key.
        // If found, return the value associated with the key; otherwise, return the default value.
        return is_array($data) && array_key_exists($key, $data)
            ? $data[$key]
            : $default;
    }

    /**
     * This method securely stores a key-value pair with an expiration time-to-live (TTL).
     * The value is encrypted before storage, and expiration metadata is stored alongside it.
     *
     * @param string $key   The unique key under which the value will be stored.
     * @param mixed  $value The value to be stored. Supported types depend on the implemented encryption mechanism.
     * @param int    $ttl   Time-to-live in seconds, determining when the data will expire.
     *
     * @return void
     */
    public function putWithTTL(string $key, mixed $value, int $ttl) : void
    {
        // Ensure the session is properly started before any storage operations.
        $this->start();

        // Store the encrypted data against the specified key in the session store.
        $this->store->put(key: $key, value: $value);

        // Store the metadata for the key including its expiration time.
        // The expiration time is calculated as the current time plus the TTL in seconds.
        $this->store->put(
            key  : "{$key}::__meta", // Append `::__meta` to key, indicating metadata storage.
            value: [
                       'expires_at' => time() + $ttl, // Store the expiration timestamp.
                   ]
        );
    }

    /**
     * Retrieves the current session bag registry, enabling access to all registered bags.
     *
     * This method exposes the `BagRegistryInterface` to consumers of the session manager,
     * enabling structured interaction with various session components.
     *
     * Example:
     * ```
     * $registry = $session->getRegistry();
     * $flashBag = $registry->flash();
     * $errorBag = $registry->errors();
     * ```
     *
     * **Key Responsibilities**:
     * 1. Encapsulation: The registry serves as a boundary for session sub-containers.
     * 2. Extensibility: Consumers of this method may register or retrieve additional session bags.
     * 3. Dependency Injection: Encourages a clean separation of concerns between session components.
     *
     * @return \Avax\HTTP\Session\Contracts\BagRegistryInterface
     *   The session bag registry instance managing all session-related sub-containers.
     */
    public function getRegistry() : BagRegistryInterface
    {
        // Return the registry enabling access to its methods for bag management.
        return $this->registry;
    }
}