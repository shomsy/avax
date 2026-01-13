<?php

declare(strict_types=1);

namespace Avax\Container\Observe\Metrics;

/**
 * Service for collecting performance metrics.
 * "Watch the pulse of the container."
 *
 * @see docs/Observe/Metrics/CollectMetrics.md#quick-summary
 */
final class CollectMetrics
{
    private array $events = [];

    /**
     * @param mixed $metrics Optional metrics collector instance used to merge snapshot output
     *
     * @see docs/Observe/Metrics/CollectMetrics.md#method-__construct
     */
    public function __construct(
        private readonly mixed $metrics = null
    ) {}

    /**
     * Record a metrics event.
     *
     * @param string $event Event name
     * @param array  $data  Event context payload
     *
     * @see docs/Observe/Metrics/CollectMetrics.md#method-record
     */
    public function record(string $event, array $data) : void
    {
        $this->events[] = [
            'event' => $event,
            'data'  => $data,
            'time'  => microtime(true),
        ];
    }

    /**
     * Collect a snapshot of current metrics.
     *
     * @return array Snapshot payload
     *
     * @see docs/Observe/Metrics/CollectMetrics.md#method-collect
     */
    public function collect() : array
    {
        $snapshot = $this->metrics instanceof MetricsCollector
            ? $this->metrics->getSnapshot()
            : [];

        $snapshot['events'] = $this->events;

        return $snapshot;
    }
}
