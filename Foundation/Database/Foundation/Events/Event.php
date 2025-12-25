<?php

declare(strict_types=1);

namespace Avax\Database\Events;

/**
 * Base definition for all domain and infrastructure events within the system.
 *
 * -- intent: provide a structured signal for system-wide state changes.
 */
abstract readonly class Event
{
    public readonly float $timestamp;

    public function __construct()
    {
        $this->timestamp = microtime(as_float: true);
    }

    /**
     * Retrieve the identifying name for this event type.
     *
     * -- intent: provide a string-based key for the event bus matching logic.
     *
     * @return string
     */
    public function getName() : string
    {
        return static::class;
    }
}
