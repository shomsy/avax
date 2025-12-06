<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events\Events;

use Avax\HTTP\Session\Features\Events\SessionEvent;
use DateTimeImmutable;

/**
 * SessionInvalidatedEvent
 *
 * Dispatched when a session is invalidated/destroyed.
 *
 * @package Avax\HTTP\Session\Features\Events\Events
 */
final readonly class SessionInvalidatedEvent extends SessionEvent
{
    /**
     * Create a new SessionInvalidatedEvent.
     *
     * @param string $oldSessionId The old session ID.
     * @param string $newSessionId The new session ID.
     *
     * @return self
     */
    public static function create(string $oldSessionId, string $newSessionId): self
    {
        return new self(
            action: 'InvalidateSession',
            context: [
                'old_session_id' => $oldSessionId,
                'new_session_id' => $newSessionId,
                'security_event' => true,
            ],
            occurredAt: new DateTimeImmutable()
        );
    }
}
