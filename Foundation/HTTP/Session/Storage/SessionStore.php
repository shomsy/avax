<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Storage;

/**
 * SessionStore Interface
 *
 * Contract for session storage backends.
 *
 * This interface defines the persistence layer for session data,
 * abstracting away the underlying storage mechanism (native PHP,
 * Redis, database, etc.).
 *
 * Enterprise Rules:
 * - Atomicity: All operations must be atomic.
 * - Idempotency: Safe to call operations multiple times.
 * - Security: Must support secure session ID generation.
 *
 * Implementations:
 * - NativeStore: PHP native sessions
 * - ArrayStore: In-memory (testing)
 * - RedisStore: Redis backend
 * - DatabaseStore: Database backend
 *
 * @package Avax\HTTP\Session\Storage
 */
interface SessionStore
{
    /**
     * Start the session.
     *
     * Initializes the session storage and makes it ready for operations.
     * Must be idempotent (safe to call multiple times).
     *
     * @return void
     */
    public function start(): void;

    /**
     * Check if session is started.
     *
     * @return bool True if session is active.
     */
    public function isStarted(): bool;

    /**
     * Get the current session ID.
     *
     * @return string The session identifier.
     */
    public function getId(): string;

    /**
     * Regenerate the session ID.
     *
     * Creates a new session ID while optionally preserving or destroying
     * the old session data.
     *
     * @param bool $deleteOldSession Whether to destroy old session data.
     *
     * @return void
     */
    public function regenerateId(bool $deleteOldSession = true): void;

    /**
     * Store a value in the session.
     *
     * @param string $key   The storage key.
     * @param mixed  $value The value to store (must be serializable).
     *
     * @return void
     */
    public function put(string $key, mixed $value): void;

    /**
     * Retrieve a value from the session.
     *
     * @param string $key     The storage key.
     * @param mixed  $default The default value if key doesn't exist.
     *
     * @return mixed The stored value or default.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Check if a key exists in the session.
     *
     * @param string $key The storage key.
     *
     * @return bool True if key exists.
     */
    public function has(string $key): bool;

    /**
     * Delete a value from the session.
     *
     * @param string $key The storage key.
     *
     * @return void
     */
    public function delete(string $key): void;

    /**
     * Get all session data.
     *
     * @return array<string, mixed> All session data.
     */
    public function all(): array;

    /**
     * Clear all session data.
     *
     * Removes all data but keeps the session active.
     *
     * @return void
     */
    public function flush(): void;
}
