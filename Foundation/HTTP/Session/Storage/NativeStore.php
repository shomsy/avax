<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Storage;

/**
 * NativeStore
 *
 * Native PHP session storage implementation.
 *
 * This store uses PHP's native $_SESSION superglobal for persistence,
 * providing a production-ready storage backend with file-based persistence.
 *
 * Enterprise Rules:
 * - Production-ready: Uses battle-tested PHP session mechanism.
 * - Security: Supports secure session configuration.
 * - Persistence: Data persists across requests.
 *
 * Usage:
 *   $store = new NativeStore();
 *   $store->start();
 *   $store->put('key', 'value');
 *
 * @package Avax\HTTP\Session\Storage
 */
final class NativeStore implements SessionStore
{
    /**
     * Start the session.
     *
     * Initializes PHP's native session mechanism if not already started.
     * Safe to call multiple times (idempotent).
     *
     * @return void
     */
    public function start(): void
    {
        // Check if session is not active.
        if (session_status() === PHP_SESSION_NONE) {
            // Start native PHP session.
            session_start();
        }
    }

    /**
     * Check if session is started.
     *
     * @return bool True if session is active.
     */
    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Get the current session ID.
     *
     * @return string The session identifier.
     */
    public function getId(): string
    {
        // Ensure session is started.
        $this->start();

        // Return session ID.
        return session_id();
    }

    /**
     * Regenerate the session ID.
     *
     * @param bool $deleteOldSession Whether to destroy old session data.
     *
     * @return void
     */
    public function regenerateId(bool $deleteOldSession = true): void
    {
        // Ensure session is started.
        $this->start();

        // Regenerate session ID.
        session_regenerate_id(delete_old_session: $deleteOldSession);
    }

    /**
     * Store a value in the session.
     *
     * @param string $key   The storage key.
     * @param mixed  $value The value to store.
     *
     * @return void
     */
    public function put(string $key, mixed $value): void
    {
        // Ensure session is started.
        $this->start();

        // Store in $_SESSION.
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieve a value from the session.
     *
     * @param string $key     The storage key.
     * @param mixed  $default The default value.
     *
     * @return mixed The stored value or default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Ensure session is started.
        $this->start();

        // Return value or default.
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a key exists.
     *
     * @param string $key The storage key.
     *
     * @return bool True if key exists.
     */
    public function has(string $key): bool
    {
        // Ensure session is started.
        $this->start();

        // Check if key exists in $_SESSION.
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Delete a value from the session.
     *
     * @param string $key The storage key.
     *
     * @return void
     */
    public function delete(string $key): void
    {
        // Ensure session is started.
        $this->start();

        // Remove from $_SESSION.
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data.
     *
     * @return array<string, mixed> All session data.
     */
    public function all(): array
    {
        // Ensure session is started.
        $this->start();

        // Return entire $_SESSION.
        return $_SESSION;
    }

    /**
     * Clear all session data.
     *
     * @return void
     */
    public function flush(): void
    {
        // Ensure session is started.
        $this->start();

        // Empty $_SESSION.
        $_SESSION = [];
    }
}
