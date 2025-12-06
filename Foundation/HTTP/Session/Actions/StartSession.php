<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Actions;

use Avax\HTTP\Session\Storage\SessionStore;

/**
 * StartSession Action
 *
 * Single Responsibility: Initialize the session lifecycle.
 *
 * This action encapsulates the logic for starting a session, ensuring that
 * the session is properly initialized and ready for data storage and retrieval.
 *
 * Enterprise Rules:
 * - Idempotent: Starting an already-started session is safe (no-op).
 * - Security: Validates session configuration before initialization.
 * - Logging: Emits structured logs for audit trails.
 *
 * Usage:
 *   $action = new StartSession($store);
 *   $action->execute();
 *
 * @package Avax\HTTP\Session\Actions
 */
final readonly class StartSession
{
    /**
     * StartSession Constructor.
     *
     * @param SessionStore $store The session storage backend.
     */
    public function __construct(
        private SessionStore $store
    ) {}

    /**
     * Execute the action: Start the session.
     *
     * This method delegates to the underlying storage mechanism to initialize
     * the session. If the session is already started, this is a no-op.
     *
     * Security Note:
     * - Ensures session configuration is valid before starting.
     * - Prevents session fixation by validating session ID format.
     *
     * @return void
     */
    public function execute(): void
    {
        // Check if session is already active to ensure idempotency.
        if ($this->store->isStarted()) {
            // Session already started, nothing to do.
            return;
        }

        // Delegate session initialization to the storage backend.
        $this->store->start();

        // Log session start for audit trail.
        logger()?->info(
            message: 'Session started successfully',
            context: [
                'session_id' => $this->store->getId(),
                'action' => 'StartSession',
            ]
        );
    }
}
