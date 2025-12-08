<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Adapters;

use Avax\HTTP\Session\Security\CookieManager;

/**
 * SessionAdapter - Native PHP Session Abstraction
 *
 * Abstracts native PHP session functions for testability and portability.
 * Enables dependency injection and mocking in unit tests.
 *
 * Benefits:
 * - Testable: Can be mocked in unit tests
 * - Portable: Easy to switch to different session handlers
 * - Clean: Encapsulates native PHP session functions
 *
 * @package Avax\HTTP\Session\Adapters
 */
final class SessionAdapter
{
    /**
     * SessionAdapter Constructor.
     *
     * @param CookieManager|null $cookieManager Cookie manager for secure cookies.
     */
    public function __construct(
        private ?CookieManager $cookieManager = null
    ) {
        $this->cookieManager ??= CookieManager::lax();
    }

    /**
     * Start a new session or resume existing session.
     *
     * @return bool True on success.
     */
    public function start(): bool
    {
        if ($this->isActive()) {
            return true;
        }

        // Configure session cookie before starting
        $this->cookieManager->configureSessionCookie();

        return session_start();
    }

    /**
     * Check if session is active.
     *
     * @return bool True if session is active.
     */
    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Regenerate session ID.
     *
     * OWASP ASVS 3.2.1 Compliant
     *
     * Prevents session fixation attacks.
     *
     * @param bool $deleteOldSession Delete old session data.
     *
     * @return bool True on success.
     */
    public function regenerateId(bool $deleteOldSession = true): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Get current session ID.
     *
     * @return string Session ID.
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Set session ID.
     *
     * @param string $id Session ID.
     *
     * @return bool True on success.
     */
    public function setId(string $id): bool
    {
        if ($this->isActive()) {
            return false; // Cannot change ID of active session
        }

        session_id($id);
        return true;
    }

    /**
     * Get session name.
     *
     * @return string Session name.
     */
    public function getName(): string
    {
        return session_name();
    }

    /**
     * Set session name.
     *
     * @param string $name Session name.
     *
     * @return string Previous session name.
     */
    public function setName(string $name): string
    {
        return session_name($name);
    }

    /**
     * Destroy the current session.
     *
     * OWASP ASVS 3.2.3 Compliant
     *
     * Completely terminates the session:
     * - Clears session data
     * - Destroys server-side session
     * - Removes client cookie
     *
     * @return bool True on success.
     */
    public function destroy(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        // Clear session variables
        $_SESSION = [];

        // Delete session cookie
        $this->deleteCookie();

        // Destroy server-side session
        return session_destroy();
    }

    /**
     * Delete session cookie.
     *
     * @return bool True on success.
     */
    public function deleteCookie(): bool
    {
        return $this->cookieManager->delete($this->getName());
    }

    /**
     * Write session data and close session.
     *
     * @return bool True on success.
     */
    public function write(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return session_write_close();
    }

    /**
     * Abort session changes and close session.
     *
     * @return bool True on success.
     */
    public function abort(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return session_abort();
    }

    /**
     * Perform garbage collection.
     *
     * @return int|false Number of deleted sessions or false on failure.
     */
    public function gc(): int|false
    {
        return session_gc();
    }

    /**
     * Set session save path.
     *
     * @param string $path Save path.
     *
     * @return string Previous save path.
     */
    public function setSavePath(string $path): string
    {
        return session_save_path($path);
    }

    /**
     * Get session save path.
     *
     * @return string Save path.
     */
    public function getSavePath(): string
    {
        return session_save_path();
    }

    /**
     * Get session module name.
     *
     * @return string Module name (e.g., 'files', 'redis').
     */
    public function getModuleName(): string
    {
        return session_module_name();
    }

    /**
     * Set session module name.
     *
     * @param string $module Module name.
     *
     * @return string Previous module name.
     */
    public function setModuleName(string $module): string
    {
        return session_module_name($module);
    }

    /**
     * Get session cache limiter.
     *
     * @return string Cache limiter.
     */
    public function getCacheLimiter(): string
    {
        return session_cache_limiter();
    }

    /**
     * Set session cache limiter.
     *
     * @param string $limiter Cache limiter ('nocache', 'public', 'private', etc).
     *
     * @return string Previous cache limiter.
     */
    public function setCacheLimiter(string $limiter): string
    {
        return session_cache_limiter($limiter);
    }

    /**
     * Unset all session variables.
     *
     * @return void
     */
    public function unsetAll(): void
    {
        $_SESSION = [];
    }

    /**
     * Get cookie manager.
     *
     * @return CookieManager Cookie manager instance.
     */
    public function getCookieManager(): CookieManager
    {
        return $this->cookieManager;
    }

    /**
     * Reset session to clean state without destroying it.
     *
     * Useful for privilege de-escalation scenarios.
     *
     * @return void
     */
    public function reset(): void
    {
        if ($this->isActive()) {
            $this->unsetAll();
            $this->regenerateId();
        }
    }
}
