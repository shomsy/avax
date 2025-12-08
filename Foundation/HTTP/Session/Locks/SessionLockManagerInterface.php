<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Locks;

interface SessionLockManagerInterface
{
    /**
     * Acquire a lock for the given session ID
     *
     * @param string $sessionId The session ID to lock
     * @param int    $timeout   Maximum time to wait for the lock (in seconds)
     *
     * @return bool True if the lock was acquired, false otherwise
     */
    public function acquire(string $sessionId, int $timeout = 30) : bool;

    /**
     * Release the lock for the given session ID
     *
     * @param string $sessionId The session ID to release the lock for
     *
     * @return bool True if the lock was released, false otherwise
     */
    public function release(string $sessionId) : bool;

    /**
     * Check if a session is currently locked
     *
     * @param string $sessionId The session ID to check
     *
     * @return bool True if the session is locked, false otherwise
     */
    public function isLocked(string $sessionId) : bool;
}
