<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events\Events;

use Avax\HTTP\Session\Features\Events\SessionEvent;
use DateTimeImmutable;

/**
 * SessionValueStoredEvent
 *
 * Dispatched when a value is stored in the session.
 *
 * @package Avax\HTTP\Session\Features\Events\Events
 */
final readonly class SessionValueStoredEvent extends SessionEvent
{
    /**
     * Create a new SessionValueStoredEvent.
     *
     * @param string   $key       The storage key.
     * @param bool     $encrypted Whether the value was encrypted.
     * @param int|null $ttl       TTL in seconds if set.
     * @param string   $namespace The namespace.
     *
     * @return self
     */
    public static function create(
        string $key,
        bool $encrypted,
        int|null $ttl,
        string $namespace
    ): self {
        return new self(
            action: 'StoreValue',
            context: [
                'key' => $key,
                'encrypted' => $encrypted,
                'ttl' => $ttl,
                'namespace' => $namespace,
            ],
            occurredAt: new DateTimeImmutable()
        );
    }
}
