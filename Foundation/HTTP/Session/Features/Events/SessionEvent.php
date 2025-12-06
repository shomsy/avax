<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events;

use DateTimeImmutable;

/**
 * SessionEvent Base Class
 *
 * Abstract base for all session-related events.
 *
 * This class provides common structure for events, ensuring
 * consistency across the event system.
 *
 * Enterprise Rules:
 * - Immutability: Events are readonly value objects.
 * - Timestamp: All events have occurrence time.
 * - Context: Rich contextual data for observability.
 *
 * @package Avax\HTTP\Session\Features\Events
 */
abstract readonly class SessionEvent
{
    /**
     * SessionEvent Constructor.
     *
     * @param string            $action     The action that triggered the event.
     * @param array<string, mixed> $context    Contextual data about the event.
     * @param DateTimeImmutable $occurredAt When the event occurred.
     */
    public function __construct(
        public string $action,
        public array $context,
        public DateTimeImmutable $occurredAt
    ) {}

    /**
     * Get event name.
     *
     * @return string The event class name.
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * Convert to array for serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event' => $this->getName(),
            'action' => $this->action,
            'context' => $this->context,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
}
