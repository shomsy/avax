<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events\Events;

use Avax\HTTP\Session\Features\Events\SessionEvent;
use DateTimeImmutable;

/**
 * SessionStartedEvent
 *
 * Dispatched when a session is successfully started.
 *
 * @package Avax\HTTP\Session\Features\Events\Events
 */
final readonly class SessionStartedEvent extends SessionEvent
{
    /**
     * Create a new SessionStartedEvent.
     *
     * @param string $sessionId The session identifier.
     *
     * @return self
     */
    public static function create(string $sessionId): self
    {
        return new self(
            action: 'StartSession',
            context: ['session_id' => $sessionId],
            occurredAt: new DateTimeImmutable()
        );
    }
}
