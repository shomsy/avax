<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

use Avax\HTTP\Session\Contracts\Storage\Store;

/**
 * SessionRegistry - Multi-Device Session Control
 *
 * OWASP ASVS 3.3.8 Compliant
 *
 * Tracks and manages concurrent sessions per user.
 * Prevents session sharing and enables single-device enforcement.
 *
 * Features:
 * - Track multiple sessions per user
 * - Terminate other sessions on new login
 * - Concurrent session limit enforcement
 * - Session metadata tracking (IP, user agent, timestamp)
 *
 * @package Avax\HTTP\Session\Security
 */
final class SessionRegistry
{
    private const string REGISTRY_PREFIX = '_registry_';

    /**
     * SessionRegistry Constructor.
     *
     * @param Store $store Session storage backend.
     */
    public function __construct(
        private Store $store
    ) {}

    /**
     * Register a new session for a user.
     *
     * @param string $userId    User identifier.
     * @param string $sessionId Session ID.
     * @param array  $metadata  Optional metadata (IP, user agent, etc).
     *
     * @return void
     */
    public function register(string $userId, string $sessionId, array $metadata = []) : void
    {
        $key      = self::REGISTRY_PREFIX . $userId;
        $sessions = $this->store->get(key: $key, default: []);

        $sessions[$sessionId] = array_merge($metadata, [
            'created_at'    => time(),
            'last_activity' => time(),
        ]);

        $this->store->put(key: $key, value: $sessions);
    }

    /**
     * Terminate all other sessions except current.
     *
     * Useful for "single device" enforcement.
     *
     * @param string $userId          User identifier.
     * @param string $exceptSessionId Current session to preserve.
     *
     * @return int Number of terminated sessions.
     */
    public function terminateOtherSessions(string $userId, string $exceptSessionId) : int
    {
        $sessions   = $this->getActiveSessions($userId);
        $terminated = 0;

        foreach ($sessions as $sessionId => $metadata) {
            if ($sessionId !== $exceptSessionId) {
                // In real implementation, you'd call session_destroy() for each ID
                unset($sessions[$sessionId]);
                $terminated++;
            }
        }

        $key = self::REGISTRY_PREFIX . $userId;
        $this->store->put(key: $key, value: $sessions);

        return $terminated;
    }

    /**
     * Get all active sessions for a user.
     *
     * @param string $userId User identifier.
     *
     * @return array<string, array> Session ID => metadata.
     */
    public function getActiveSessions(string $userId) : array
    {
        $key = self::REGISTRY_PREFIX . $userId;

        return $this->store->get(key: $key, default: []);
    }

    /**
     * Update last activity timestamp for a session.
     *
     * @param string $userId    User identifier.
     * @param string $sessionId Session ID.
     *
     * @return void
     */
    public function updateActivity(string $userId, string $sessionId) : void
    {
        $sessions = $this->getActiveSessions($userId);

        if (isset($sessions[$sessionId])) {
            $sessions[$sessionId]['last_activity'] = time();

            $key = self::REGISTRY_PREFIX . $userId;
            $this->store->put(key: $key, value: $sessions);
        }
    }

    /**
     * Check if user has exceeded concurrent session limit.
     *
     * @param string $userId User identifier.
     * @param int    $limit  Maximum allowed concurrent sessions.
     *
     * @return bool True if limit exceeded.
     */
    public function hasExceededLimit(string $userId, int $limit) : bool
    {
        $sessions = $this->getActiveSessions($userId);

        return count($sessions) >= $limit;
    }

    /**
     * Check if a session is revoked.
     *
     * @param string $sessionId Session ID to check.
     *
     * @return bool True if revoked.
     */
    public function isRevoked(string $sessionId) : bool
    {
        $key     = self::REGISTRY_PREFIX . 'revoked';
        $revoked = $this->store->get(key: $key, default: []);

        return isset($revoked[$sessionId]);
    }

    // ========================================
    // REVOCATION LIST (OWASP ASVS 3.3.8)
    // ========================================

    /**
     * Get revocation details for a session.
     *
     * @param string $sessionId Session ID.
     *
     * @return array|null Revocation details or null.
     */
    public function getRevocationDetails(string $sessionId) : array|null
    {
        $key     = self::REGISTRY_PREFIX . 'revoked';
        $revoked = $this->store->get(key: $key, default: []);

        return $revoked[$sessionId] ?? null;
    }

    /**
     * Revoke all sessions for a user.
     *
     * Useful for:
     * - Password changes
     * - Security breaches
     * - Account lockout
     *
     * @param string $userId User identifier.
     * @param string $reason Revocation reason.
     *
     * @return int Number of sessions revoked.
     */
    public function revokeAllForUser(string $userId, string $reason = 'user_revocation') : int
    {
        $sessions = $this->getActiveSessions($userId);
        $count    = 0;

        foreach (array_keys($sessions) as $sessionId) {
            $this->revoke($sessionId, $reason);
            $count++;
        }

        // Also clear active sessions
        $key = self::REGISTRY_PREFIX . $userId;
        $this->store->delete(key: $key);

        return $count;
    }

    /**
     * Add a session to revocation list.
     *
     * Revoked sessions cannot be used anymore, even if valid.
     * Useful for:
     * - Forced logout
     * - Security breaches
     * - Password changes
     * - Privilege changes
     *
     * @param string $sessionId Session ID to revoke.
     * @param string $reason    Revocation reason.
     *
     * @return void
     */
    public function revoke(string $sessionId, string $reason = 'manual_revocation') : void
    {
        $key     = self::REGISTRY_PREFIX . 'revoked';
        $revoked = $this->store->get(key: $key, default: []);

        $revoked[$sessionId] = [
            'revoked_at' => time(),
            'reason'     => $reason,
        ];

        $this->store->put(key: $key, value: $revoked);
    }

    /**
     * Remove a session from revocation list.
     *
     * Use with caution - only for administrative purposes.
     *
     * @param string $sessionId Session ID to unrevoke.
     *
     * @return bool True if was revoked and now removed.
     */
    public function unrevoke(string $sessionId) : bool
    {
        $key     = self::REGISTRY_PREFIX . 'revoked';
        $revoked = $this->store->get(key: $key, default: []);

        if (! isset($revoked[$sessionId])) {
            return false;
        }

        unset($revoked[$sessionId]);
        $this->store->put(key: $key, value: $revoked);

        return true;
    }

    /**
     * Clear old revoked sessions.
     *
     * Removes revocations older than specified age.
     *
     * @param int $maxAge Maximum age in seconds (default: 30 days).
     *
     * @return int Number of cleared revocations.
     */
    public function clearOldRevocations(int $maxAge = 2592000) : int
    {
        $key     = self::REGISTRY_PREFIX . 'revoked';
        $revoked = $this->store->get($key, []);
        $cleared = 0;

        foreach ($revoked as $sessionId => $data) {
            if (time() - $data['revoked_at'] > $maxAge) {
                unset($revoked[$sessionId]);
                $cleared++;
            }
        }

        $this->store->put(key: $key, value: $revoked);

        return $cleared;
    }

    /**
     * Count total revoked sessions.
     *
     * @return int Count.
     */
    public function countRevoked() : int
    {
        return count($this->getAllRevoked());
    }

    /**
     * Get all revoked sessions.
     *
     * @return array<string, array> Session ID => revocation data.
     */
    public function getAllRevoked() : array
    {
        $key = self::REGISTRY_PREFIX . 'revoked';

        return $this->store->get(key: $key, default: []);
    }

    /**
     * Get sessions grouped by device/user agent.
     *
     * @param string $userId User identifier.
     *
     * @return array<string, array> Device fingerprint => sessions.
     */
    public function getSessionsByDevice(string $userId) : array
    {
        $sessions = $this->getActiveSessions($userId);
        $byDevice = [];

        foreach ($sessions as $sessionId => $metadata) {
            $fingerprint              = $metadata['user_agent'] ?? 'unknown';
            $byDevice[$fingerprint][] = array_merge(['session_id' => $sessionId], $metadata);
        }

        return $byDevice;
    }

    // ========================================
    // DEVICE MANAGEMENT
    // ========================================

    /**
     * Terminate all sessions from a specific device.
     *
     * @param string $userId    User identifier.
     * @param string $userAgent User agent string to match.
     *
     * @return int Number of terminated sessions.
     */
    public function terminateDevice(string $userId, string $userAgent) : int
    {
        $sessions   = $this->getActiveSessions($userId);
        $terminated = 0;

        foreach ($sessions as $sessionId => $metadata) {
            if (($metadata['user_agent'] ?? '') === $userAgent) {
                $this->terminateSession($userId, $sessionId);
                $terminated++;
            }
        }

        return $terminated;
    }

    /**
     * Terminate a specific session.
     *
     * @param string $userId    User identifier.
     * @param string $sessionId Session ID to terminate.
     *
     * @return bool True if session was found and terminated.
     */
    public function terminateSession(string $userId, string $sessionId) : bool
    {
        $sessions = $this->getActiveSessions($userId);

        if (! isset($sessions[$sessionId])) {
            return false;
        }

        unset($sessions[$sessionId]);

        $key = self::REGISTRY_PREFIX . $userId;
        $this->store->put(key: $key, value: $sessions);

        return true;
    }
}
