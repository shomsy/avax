<?php

declare(strict_types=1);

namespace Avax\Database\Events;

use Avax\Database\Support\SequenceTracker;

/**
 * Abstract base class for all database telemetry events.
 *
 * Provides correlation ID, timestamp, and sequence tracking for audit trails.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Telemetry.md
 */
abstract readonly class Event
{
    /** @var float The exact moment (with microseconds) this shout was made. */
    public float $timestamp;

    /** @var string The "Luggage Tag" that connects this event to the current request. */
    public string $correlationId;

    /** @var int The "Order Number" (Sequence) to keep events in the right chronological chain. */
    public int $sequence;

    /**
     * @param string $correlationId The Trace ID representing the active work context.
     */
    public function __construct(string $correlationId)
    {
        $this->timestamp     = microtime(as_float: true);
        $this->correlationId = $correlationId;
        $this->sequence      = SequenceTracker::next();
    }

    /**
     * Get the technical "Type" (Name) of this event.
     *
     * @return string The full name of the event class.
     */
    public function getName(): string
    {
        return static::class;
    }
}
