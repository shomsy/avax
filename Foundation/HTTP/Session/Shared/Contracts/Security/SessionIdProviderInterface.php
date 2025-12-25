<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Contracts\Security;

/**
 * SessionIdProviderInterface
 *
 * Contract for session ID generation and management.
 *
 * Implementations:
 * - NativeSessionIdProvider: Uses PHP's native session functions
 * - CustomSessionIdProvider: Custom session ID management
 *
 * @package Avax\HTTP\Session\Shared\Contracts\Security
 */
interface SessionIdProviderInterface
{
    /**
     * Generate a new session ID.
     *
     * @return string The generated session ID.
     */
    public function generate() : string;

    /**
     * Regenerate the current session ID.
     *
     * This is critical for preventing session fixation attacks.
     * Should be called on login, privilege elevation, etc.
     *
     * @param bool $deleteOld Whether to delete the old session data.
     *
     * @return string The new session ID.
     */
    public function regenerate(bool $deleteOld = true) : string;

    /**
     * Get the current session ID.
     *
     * @return string The current session ID.
     */
    public function current() : string;

    /**
     * Set a custom session ID.
     *
     * @param string $id The session ID to set.
     *
     * @return void
     */
    public function set(string $id) : void;

    /**
     * Check if a session is active.
     *
     * @return bool True if session is active.
     */
    public function isActive() : bool;
}
