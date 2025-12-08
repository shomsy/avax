<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Storage;

use Avax\HTTP\Session\Contracts\Storage\Store;
use Avax\HTTP\Session\Locks\SessionLockManagerInterface;
use SessionUpdateTimestampHandlerInterface;

/**
 * NativeStore - PHP Native Session Storage
 *
 * Production-ready implementation using PHP's native session functions with enhanced features:
 * - Session locking for concurrent request safety
 * - OWASP ASVS 3.3.1+ compliant security
 * - Configurable session settings
 * - Implements SessionUpdateTimestampHandlerInterface for better performance
 *
 * @package Avax\HTTP\Session
 */
final class NativeStore extends AbstractStore implements SessionUpdateTimestampHandlerInterface
{
    private ?SessionLockManagerInterface $lockManager;
    private array $sessionData = [];
    private bool $isLocked = false;

    /**
     * NativeStore Constructor.
     *
     * @param \Avax\HTTP\Session\Security\CookieManager|null $cookieManager Cookie manager instance
     * @param SessionLockManagerInterface|null $lockManager Optional lock manager for concurrent access control
     */
    public function __construct(
        private readonly ?\Avax\HTTP\Session\Security\CookieManager $cookieManager = null,
        ?SessionLockManagerInterface $lockManager = null
    ) {
        $this->lockManager = $lockManager;
    }

    /**
     * Start the session if not already started
     *
     * @throws \Avax\HTTP\Session\Exceptions\SessionStartException If session cannot be started
     */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $this->cookieManager?->configureSessionCookie();
        
        // Apply security settings
        $this->configureSessionIni();

        if (!session_start()) {
            throw new \Avax\HTTP\Session\Exceptions\SessionStartException(
                'Failed to start session: ' . (error_get_last()['message'] ?? 'Unknown error')
            );
        }

        $this->sessionData = &$_SESSION;
        $this->acquireLock(session_id());
    }

    /**
     * Configure PHP session settings
     */
    private function configureSessionIni(): void
    {
        $config = [
            'session.cookie_secure' => '1',
            'session.cookie_httponly' => '1',
            'session.cookie_samesite' => 'Strict',
            'session.use_strict_mode' => '1',
            'session.use_only_cookies' => '1',
            'session.use_trans_sid' => '0',
            'session.cookie_lifetime' => '0', // Until browser close
            'session.gc_maxlifetime' => (string)(60 * 60 * 24), // 24 hours
            'session.gc_probability' => '1',
            'session.gc_divisor' => '1000',
        ];

        foreach ($config as $key => $value) {
            ini_set($key, $value);
        }
    }

    /**
     * Acquire a lock for the current session
     */
    private function acquireLock(string $sessionId): void
    {
        if ($this->lockManager && !$this->lockManager->acquire($sessionId)) {
            throw new \RuntimeException('Could not acquire session lock');
        }
        $this->isLocked = true;
    }

    /**
     * Release the current session lock
     */
    private function releaseLock(string $sessionId): void
    {
        if ($this->lockManager && $this->isLocked) {
            $this->lockManager->release($sessionId);
            $this->isLocked = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        return $this->sessionData[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $this->sessionData[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();
        return array_key_exists($key, $this->sessionData);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): void
    {
        $this->ensureStarted();
        unset($this->sessionData[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        $this->ensureStarted();
        return $this->sessionData;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        $this->ensureStarted();
        $this->sessionData = [];
    }

    /**
     * Close the session and release locks
     */
    public function close(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $sessionId = session_id();
            session_write_close();
            $this->releaseLock($sessionId);
        }
    }

    /**
     * Destroy the session
     */
    public function destroy(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $sessionId = session_id();
        $this->sessionData = [];
        session_destroy();
        $this->releaseLock($sessionId);
        
        return true;
    }

    // SessionUpdateTimestampHandlerInterface methods

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId): bool
    {
        return preg_match('/^[a-zA-Z0-9,-]{22,256}$/', $sessionId) === 1;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $data): bool
    {
        return true; // Native PHP handles this
    }

    /**
     * Ensure the session is started
     */
    private function ensureStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->start();
        }
    }

    /**
     * Destructor - ensures session is properly closed
     */
    public function __destruct()
    {
        $this->close();
    }
}
