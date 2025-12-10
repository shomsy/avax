<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

use Avax\HTTP\Session\Contracts\Storage\Store;
use SensitiveParameter;

/**
 * 🧠 SessionRegistry — Multi-Device Session Management
 *
 * ------------------------------------------------------------------------
 * THEORY (for dummies):
 * ------------------------------------------------------------------------
 * Imagine every user has a small “notebook” 📒 where all their active
 * sessions are recorded — each page is one device or browser where
 * they’re currently logged in.
 *
 * That notebook is the *Session Registry*.
 *
 * Its job is to:
 * - Keep track of all active sessions per user 👤
 * - Detect if a session is too old or idle ⏳
 * - Let you force logout from other devices 🪟
 * - Revoke or block compromised sessions 🚫
 *
 * Think of it as your “control room” for all user logins.
 * ------------------------------------------------------------------------
 *
 * 🛡️ Why it exists:
 * Without a registry, you can’t:
 * - Prevent stolen tokens from staying valid forever
 * - Enforce “single-device login” or session limits
 * - View all devices a user is logged in from
 * - Revoke access after password change or breach
 *
 * This is required by OWASP ASVS 3.3.8 — “Applications must allow
 * sessions to be revoked and limit concurrent sessions.”
 *
 * ------------------------------------------------------------------------
 * TECHNICAL OVERVIEW:
 * ------------------------------------------------------------------------
 * - All data is stored inside your Session Store (FileStore / RedisStore)
 * - Each user has their own “registry key”: _registry_{userId}
 * - Each session entry contains metadata like IP, user-agent, timestamps
 * - A separate “revoked list” stores sessions that are invalid forever
 *
 * 💡 Think of it as two tables:
 *   1️⃣ Active sessions per user
 *   2️⃣ Revoked (banned) sessions globally
 * ------------------------------------------------------------------------
 */
final class SessionRegistry
{
    private const string REGISTRY_PREFIX = '_registry_';

    public function __construct(
        private readonly Store $store
    ) {}

    // ============================================================
    // 1️⃣ REGISTRATION — adding new sessions
    // ============================================================

    /**
     * Register a new session for a user.
     *
     * 🧠 What happens:
     * Every time a user logs in, we record:
     * - Which session ID they got
     * - From what IP and device (metadata)
     * - When it was created
     */
    public function register(string $userId, #[SensitiveParameter] string $sessionId, array $metadata = []) : void
    {
        $key      = self::REGISTRY_PREFIX . $userId;
        $sessions = $this->store->get(key: $key, default: []);

        $sessions[$sessionId] = array_merge($metadata, [
            'created_at'    => time(),
            'last_activity' => time(),
        ]);

        $this->store->put(key: $key, value: $sessions);
    }

    // ============================================================
    // 2️⃣ ACTIVITY — updating and checking
    // ============================================================

    /**
     * Update last activity timestamp.
     *
     * 💬 Think of this as “heartbeat” — every time user interacts,
     * we update the time so we know they’re still alive 🫀.
     */
    public function updateActivity(string $userId, #[SensitiveParameter] string $sessionId) : void
    {
        $sessions = $this->getActiveSessions($userId);
        if (! isset($sessions[$sessionId])) {
            return;
        }

        $sessions[$sessionId]['last_activity'] = time();
        $this->store->put(key: self::REGISTRY_PREFIX . $userId, value: $sessions);
    }

    /**
     * Retrieve all active sessions for a user.
     */
    public function getActiveSessions(string $userId) : array
    {
        return $this->store->get(key: self::REGISTRY_PREFIX . $userId, default: []);
    }

    /**
     * Get metadata for a specific session.
     */
    public function getSessionMetadata(string $userId, #[SensitiveParameter] string $sessionId) : array|null
    {
        $sessions = $this->getActiveSessions($userId);

        return $sessions[$sessionId] ?? null;
    }

    /**
     * Terminate all sessions except the current one.
     *
     * 🧠 Useful when user logs in again and you want
     * to “kick out” other devices — classic “single device login”.
     */
    public function terminateOtherSessions(string $userId, #[SensitiveParameter] string $exceptSessionId) : int
    {
        $sessions   = $this->getActiveSessions($userId);
        $terminated = 0;

        foreach ($sessions as $sessionId => $meta) {
            if ($sessionId !== $exceptSessionId) {
                unset($sessions[$sessionId]);
                $terminated++;
            }
        }

        $this->store->put(key: self::REGISTRY_PREFIX . $userId, value: $sessions);

        return $terminated;
    }

    // ============================================================
    // 3️⃣ TERMINATION — ending sessions
    // ============================================================

    /**
     * Terminate one specific session.
     */
    public function terminateSession(string $userId, #[SensitiveParameter] string $sessionId) : bool
    {
        $sessions = $this->getActiveSessions($userId);
        if (! isset($sessions[$sessionId])) {
            return false;
        }

        unset($sessions[$sessionId]);
        $this->store->put(key: self::REGISTRY_PREFIX . $userId, value: $sessions);

        return true;
    }

    /**
     * Terminate all sessions from a given device / browser.
     */
    public function terminateDevice(string $userId, string $userAgent) : int
    {
        $sessions   = $this->getActiveSessions($userId);
        $terminated = 0;

        foreach ($sessions as $id => $meta) {
            if (($meta['user_agent'] ?? '') === $userAgent) {
                unset($sessions[$id]);
                $terminated++;
            }
        }

        $this->store->put(key: self::REGISTRY_PREFIX . $userId, value: $sessions);

        return $terminated;
    }

    /**
     * Check if user exceeded allowed number of sessions.
     *
     * Example: allow only 2 devices per account.
     */
    public function hasExceededLimit(string $userId, int $limit) : bool
    {
        return $this->countActiveSessions($userId) >= $limit;
    }

    // ============================================================
    // 4️⃣ LIMITS — enforcing concurrent session limits
    // ============================================================

    /**
     * Count how many sessions user currently has.
     */
    public function countActiveSessions(string $userId) : int
    {
        return count($this->getActiveSessions($userId));
    }

    // ============================================================
    // 5️⃣ REVOCATION LIST — permanently blocked sessions
    // ============================================================

    /**
     * Add a session to global revocation list.
     *
     * 💬 Once revoked, a session is forever invalid — even if cookie exists.
     * Typical use cases:
     * - Password change
     * - Security breach
     * - Manual admin logout
     */
    public function revoke(#[SensitiveParameter] string $sessionId, string $reason = 'manual_revocation') : void
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
     * Check if a session is revoked.
     */
    public function isRevoked(#[SensitiveParameter] string $sessionId) : bool
    {
        return isset($this->getAllRevoked()[$sessionId]);
    }

    /**
     * Get the global list of revoked sessions.
     *
     * 🧠 Purpose:
     * Exposes the complete revocation table so higher-level components
     * (admin panels, audit tools, security dashboards) can inspect which
     * session IDs are permanently blocked and why.
     *
     * 💬 Think of it as:
     * “Show me the blacklist of all sessions that are not allowed to log in
     * anymore, regardless of cookie or token state.”
     *
     * @return array<string, array{
     *     revoked_at:int,
     *     reason:string
     * }> Map of session ID to revocation metadata.
     */
    public function getAllRevoked() : array
    {
        return $this->store->get(key: self::REGISTRY_PREFIX . 'revoked', default: []);
    }

    /**
     * Remove session from revocation list.
     */
    public function unrevoke(#[SensitiveParameter] string $sessionId) : bool
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
     * Clear old revoked sessions (default 30 days).
     */
    public function clearOldRevocations(int $maxAge = 2_592_000) : int
    {
        $key     = self::REGISTRY_PREFIX . 'revoked';
        $revoked = $this->store->get(key: $key, default: []);
        $cleared = 0;

        foreach ($revoked as $id => $meta) {
            if (time() - $meta['revoked_at'] > $maxAge) {
                unset($revoked[$id]);
                $cleared++;
            }
        }

        $this->store->put(key: $key, value: $revoked);

        return $cleared;
    }

    /**
     * Count the total number of revoked sessions.
     *
     * This method inspects the global revocation list and returns
     * how many session IDs are currently marked as revoked.
     *
     * @return int Number of revoked sessions.
     */
    public function countRevoked() : int
    {
        return count($this->getAllRevoked());
    }

    /**
     * Get detailed revocation metadata for a specific session.
     *
     * 🧠 Purpose:
     * Allows security and audit layers to understand *why* a session was
     * revoked (e.g. password change, breach response, manual admin action).
     *
     * 💬 Think of it as:
     * “Tell me the story behind this session ID — when it was blocked and for
     * what reason.”
     *
     * @param string $sessionId The session identifier to inspect.
     *
     * @return array<string, mixed>|null Revocation metadata or null if not revoked.
     */
    public function getRevocationDetails(#[SensitiveParameter] string $sessionId) : array|null
    {
        return $this->getAllRevoked()[$sessionId] ?? null;
    }

    // ============================================================
    // 6️⃣ MAINTENANCE — cleanup and purge
    // ============================================================

    /**
     * Remove all session records for a user — including revoked ones.
     * Use this when deleting user accounts completely.
     */
    public function purgeUser(string $userId) : void
    {
        $this->store->delete(self::REGISTRY_PREFIX . $userId);

        $revoked = $this->getAllRevoked();
        foreach ($revoked as $sid => $meta) {
            if (($meta['user_id'] ?? null) === $userId) {
                unset($revoked[$sid]);
            }
        }

        $this->store->put(key: self::REGISTRY_PREFIX . 'revoked', value: $revoked);
    }

    /**
     * Group user sessions by device (user-agent).
     */
    public function getSessionsByDevice(string $userId) : array
    {
        $sessions = $this->getActiveSessions($userId);
        $devices  = [];

        foreach ($sessions as $sid => $meta) {
            $fingerprint             = $meta['user_agent'] ?? 'unknown';
            $devices[$fingerprint][] = ['session_id' => $sid] + $meta;
        }

        return $devices;
    }
}
