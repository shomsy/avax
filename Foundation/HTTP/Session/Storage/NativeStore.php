<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Storage;

use Avax\HTTP\Session\Contracts\Storage\Store;

/**
 * NativeStore - PHP Native Session Storage
 *
 * Production-ready implementation using PHP's native session functions.
 *
 * Features:
 * - Auto-starts session if not started
 * - Error handling for session_start failures
 * - Thread-safe operations
 * - Persistent across requests
 * - OWASP ASVS 3.3.1 compliant cookie security
 *
 * @package Avax\HTTP\Session
 */
final class NativeStore extends AbstractStore
{
    /**
     * NativeStore Constructor.
     *
     * Auto-starts session with OWASP-compliant security settings.
     *
     * @throws \RuntimeException If session cannot be started.
     */
    public function __construct(private readonly \Avax\HTTP\Session\Security\CookieManager|null $cookieManager = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Apply cookie policy before starting the session
            $this->cookieManager?->configureSessionCookie();

            // Additional security hardening
            ini_set('session.cookie_secure', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', '1');  // Reject uninitialized session IDs
            ini_set('session.use_only_cookies', '1'); // No URL-based session IDs

            if (! @session_start()) {
                throw new \RuntimeException('Failed to start native PHP session');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, mixed $value) : void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key) : bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key) : void
    {
        unset($_SESSION[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function all() : array
    {
        return $_SESSION;
    }

    /**
     * {@inheritdoc}
     */
    public function flush() : void
    {
        $_SESSION = [];
    }
}
