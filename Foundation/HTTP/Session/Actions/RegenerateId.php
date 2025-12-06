<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Actions;

use Avax\HTTP\Session\Storage\SessionStore;

/**
 * RegenerateId Action
 *
 * Single Responsibility: Regenerate the session ID for security.
 *
 * This action handles session ID regeneration, a critical security measure
 * to prevent session fixation and session hijacking attacks.
 *
 * Enterprise Rules:
 * - Security: Always called after authentication state changes.
 * - Audit: Logs regeneration events for security monitoring.
 * - Configurable: Can preserve or destroy old session data.
 *
 * Usage:
 *   $action = new RegenerateId($store);
 *   $action->execute(deleteOldSession: true);
 *
 * Common Use Cases:
 * - After successful login
 * - After privilege escalation
 * - Periodically during long sessions
 * - After sensitive operations
 *
 * @package Avax\HTTP\Session\Actions
 */
final readonly class RegenerateId
{
    /**
     * RegenerateId Constructor.
     *
     * @param SessionStore $store The session storage backend.
     */
    public function __construct(
        private SessionStore $store
    ) {}

    /**
     * Execute the action: Regenerate session ID.
     *
     * This method regenerates the session ID while optionally preserving
     * or destroying the old session data.
     *
     * Security Note:
     * - Regenerating after login prevents session fixation attacks.
     * - Deleting old session prevents session hijacking via old ID.
     *
     * @param bool $deleteOldSession Whether to destroy the old session data.
     *                               Default: true (recommended for security).
     *
     * @return void
     */
    public function execute(bool $deleteOldSession = true): void
    {
        // Capture current session ID for audit logging.
        $oldSessionId = $this->store->getId();

        // Regenerate the session ID.
        // If deleteOldSession is true, the old session file is destroyed.
        $this->store->regenerateId(deleteOldSession: $deleteOldSession);

        // Capture new session ID.
        $newSessionId = $this->store->getId();

        // Log session ID regeneration for security audit trail.
        logger()?->info(
            message: 'Session ID regenerated successfully',
            context: [
                'old_session_id' => $oldSessionId,
                'new_session_id' => $newSessionId,
                'old_session_deleted' => $deleteOldSession,
                'action' => 'RegenerateId',
                'security_event' => true,
            ]
        );
    }
}
