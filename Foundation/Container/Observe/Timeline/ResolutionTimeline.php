<?php

declare(strict_types=1);

namespace Avax\Container\Observe\Timeline;

/**
 * High-performance timeline tracker for dependency injection resolution events.
 *
 * This class provides comprehensive performance monitoring and diagnostics for the
 * container's service resolution process. It implements a circular buffer to track
 * resolution events with microsecond precision, enabling detailed performance analysis,
 * memory leak detection, and optimization of dependency injection operations.
 *
 * @see docs/Observe/Timeline/ResolutionTimeline.md#quick-summary
 */
class ResolutionTimeline
{
    /** @var array<int, array<string, mixed>> */
    private array $events = [];

    private int $depth = 0;

    private int $maxEvents = 10_000; // Prevent memory leaks in long-running apps

    /**
     * Start tracking a resolution event and return its identifier.
     *
     * @param string $abstract The service identifier being resolved
     *
     * @return int Unique event ID for later completion with end()
     */
    public function start(string $abstract) : int
    {
        $this->depth++;
        $id = count($this->events);

        // Prevent memory leaks in long-running applications
        if ($id >= $this->maxEvents) {
            // Keep only the most recent half of events
            $this->events = array_slice($this->events, $this->maxEvents / 2);
            $id           = count($this->events);
        }

        $this->events[] = [
            'id'           => $id,
            'abstract'     => $abstract,
            'start'        => microtime(as_float: true),
            'end'          => null,
            'depth'        => $this->depth,
            'memory_start' => memory_get_usage(),
        ];

        return $id;
    }

    /**
     * Mark a resolution event as completed and capture its duration.
     *
     * @param int $id The event ID returned by start()
     */
    public function end(int $id) : void
    {
        if (isset($this->events[$id]) && $this->events[$id]['end'] === null) {
            $this->events[$id]['end']          = microtime(as_float: true);
            $this->events[$id]['duration_ms']  = ($this->events[$id]['end'] - $this->events[$id]['start']) * 1000;
            $this->events[$id]['memory_delta'] = memory_get_usage() - $this->events[$id]['memory_start'];
        }

        if ($this->depth > 0) {
            $this->depth--;
        }
    }

    /**
     * Return the recorded timeline events.
     *
     * @return array<array<string, mixed>>
     */
    public function getEvents() : array
    {
        return $this->events;
    }

    /**
     * Return the slowest resolution events for surface debugging hotspots.
     *
     * @param int $limit Maximum number of events to return (default: 5)
     *
     * @return array The slowest resolution events
     */
    public function getSlowest(int $limit = 5) : array
    {
        $sorted = $this->events;
        usort($sorted, static fn($a, $b) : int => ($b['duration_ms'] ?? 0) <=> ($a['duration_ms'] ?? 0));

        return array_slice($sorted, 0, $limit);
    }

    /**
     * Clear all recorded events.
     */
    public function clear() : void
    {
        $this->events = [];
        $this->depth  = 0;
    }

    /**
     * Set maximum number of events to track before automatic cleanup.
     *
     * @param int $max Maximum events to track (minimum: 100)
     */
    public function setMaxEvents(int $max) : void
    {
        $this->maxEvents = max(100, $max);
    }
}
