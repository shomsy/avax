<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events\Events;

use Avax\HTTP\Session\Features\Events\SessionEvent;
use DateTimeImmutable;

/**
 * SessionExpiredEvent
 *
 * Dispatched when a session expires.
 *
 * @package Avax\HTTP\Session\Features\Events\Events
 */
final readonly class SessionExpiredEvent extends SessionEvent
{
    /**
     * Create new SessionExpiredEvent.
     *
     * @param string $sessionId The session ID.
     * @param string $reason    Expiration reason.
     *
     * @return self
     */
    public static function create(string $sessionId, string $reason = 'ttl_expired'): self
    {
        return new self(
            action: 'SessionExpired',
            context: [
                'session_id' => $sessionId,
                'reason' => $reason,
            ],
            occurredAt: new DateTimeImmutable()
        );
    }
}
