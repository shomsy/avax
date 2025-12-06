<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events\Events;

use Avax\HTTP\Session\Features\Events\SessionEvent;
use DateTimeImmutable;

/**
 * SessionFlushedEvent
 *
 * Dispatched when session is flushed.
 *
 * @package Avax\HTTP\Session\Features\Events\Events
 */
final readonly class SessionFlushedEvent extends SessionEvent
{
    /**
     * Create new SessionFlushedEvent.
     *
     * @param string $sessionId The session ID.
     *
     * @return self
     */
    public static function create(string $sessionId): self
    {
        return new self(
            action: 'SessionFlushed',
            context: ['session_id' => $sessionId],
            occurredAt: new DateTimeImmutable()
        );
    }
}
