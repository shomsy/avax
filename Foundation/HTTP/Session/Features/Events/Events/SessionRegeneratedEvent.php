<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events\Events;

use Avax\HTTP\Session\Features\Events\SessionEvent;
use DateTimeImmutable;

/**
 * SessionRegeneratedEvent
 *
 * Dispatched when session ID is regenerated.
 *
 * @package Avax\HTTP\Session\Features\Events\Events
 */
final readonly class SessionRegeneratedEvent extends SessionEvent
{
    /**
     * Create new SessionRegeneratedEvent.
     *
     * @param string $oldSessionId The old session ID.
     * @param string $newSessionId The new session ID.
     *
     * @return self
     */
    public static function create(string $oldSessionId, string $newSessionId): self
    {
        return new self(
            action: 'SessionRegenerated',
            context: [
                'old_session_id' => $oldSessionId,
                'new_session_id' => $newSessionId,
                'security_event' => true,
            ],
            occurredAt: new DateTimeImmutable()
        );
    }
}
