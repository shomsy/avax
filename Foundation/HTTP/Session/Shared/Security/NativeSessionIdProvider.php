<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security;

use Avax\HTTP\Session\Shared\Contracts\Security\SessionIdProviderInterface;

/**
 * NativeSessionIdProvider
 *
 * Uses PHP's native session functions for session ID management.
 *
 * This is the recommended provider for most applications as it:
 * - Integrates with PHP's session handling
 * - Works with session handlers (files, Redis, etc.)
 * - Provides built-in security features
 */
final class NativeSessionIdProvider implements SessionIdProviderInterface
{
    /**
     * Generate a new session ID.
     *
     * @return string The generated session ID.
     */
    public function generate(): string
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return session_id();
    }

    /**
     * Regenerate the current session ID.
     *
     * Uses PHP's session_regenerate_id() for secure regeneration.
     *
     * @param  bool  $deleteOld  Whether to delete the old session data.
     * @return string The new session ID.
     */
    public function regenerate(bool $deleteOld = true): string
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerate ID
        session_regenerate_id(delete_old_session: $deleteOld);

        return session_id();
    }

    /**
     * Get the current session ID.
     *
     * @return string The current session ID.
     */
    public function current(): string
    {
        // Start session if needed
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return session_id();
    }

    /**
     * Set a custom session ID.
     *
     * @param  string  $id  The session ID to set.
     */
    public function set(string $id): void
    {
        // Can only set ID before session starts
        if (session_status() === PHP_SESSION_NONE) {
            session_id(id: $id);
            session_start();
        }
    }

    /**
     * Check if a session is active.
     *
     * @return bool True if session is active.
     */
    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}
