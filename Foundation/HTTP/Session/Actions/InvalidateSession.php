<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Actions;

use Avax\HTTP\Session\Storage\SessionStore;

/**
 * InvalidateSession Action
 *
 * Single Responsibility: Securely destroy the current session.
 *
 * This action handles complete session invalidation, which includes:
 * - Clearing all session data
 * - Regenerating the session ID (prevents session fixation)
 * - Destroying the session on the storage backend
 *
 * Enterprise Rules:
 * - Security: Always regenerates ID to prevent session fixation attacks.
 * - Audit: Logs invalidation for security monitoring.
 * - Idempotent: Safe to call multiple times.
 *
 * Usage:
 *   $action = new InvalidateSession($store);
 *   $action->execute();
 *
 * Common Use Cases:
 * - User logout
 * - Session timeout
 * - Security breach response
 *
 * @package Avax\HTTP\Session\Actions
 */
final readonly class InvalidateSession
{
    /**
     * InvalidateSession Constructor.
     *
     * @param SessionStore $store The session storage backend.
     */
    public function __construct(
        private SessionStore $store
    ) {}

    /**
     * Execute the action: Invalidate the session.
     *
     * This method performs a complete session teardown:
     * 1. Captures current session ID for logging
     * 2. Flushes all session data
     * 3. Regenerates session ID
     * 4. Logs the invalidation event
     *
     * Security Note:
     * - Regenerating the ID prevents session fixation attacks.
     * - All data is cleared to prevent information leakage.
     *
     * @return void
     */
    public function execute(): void
    {
        // Capture current session ID for audit logging.
        $oldSessionId = $this->store->getId();

        // Flush all session data to ensure clean slate.
        $this->store->flush();

        // Regenerate session ID and destroy old session file.
        // This prevents session fixation attacks.
        $this->store->regenerateId(deleteOldSession: true);

        // Log session invalidation for security audit trail.
        logger()?->info(
            message: 'Session invalidated successfully',
            context: [
                'old_session_id' => $oldSessionId,
                'new_session_id' => $this->store->getId(),
                'action' => 'InvalidateSession',
                'security_event' => true,
            ]
        );
    }
}
