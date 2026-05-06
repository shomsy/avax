<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events;

use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\SequenceTracker;

/**
 * Abstract base class for all database telemetry events.
 *
 * Provides correlation ID, timestamp, and sequence tracking for audit trails.
 *
 * @see /docs/Foundation/Database/Concepts/Telemetry.md
 */
abstract readonly class Event
{
    /** @var float The exact moment (with microseconds) this shout was made. */
    public float $timestamp;

    /** @var int The "Order Number" (Sequence) to keep events in the right chronological chain. */
    public int $sequence;

    /**
     * @param  string  $correlationId  The Trace ID representing the active work context.
     */
    public function __construct(public string $correlationId)
    {
        $this->timestamp = microtime(as_float: true);
        $this->sequence = SequenceTracker::next();
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
