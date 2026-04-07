<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Observability\Metrics;

/**
 * Simple in-memory metrics collector used by the runtime.
 */
final class CollectMetrics
{
    private array $events = [];

    public function __construct(
        private readonly mixed $metrics = null
    ) {}

    public function record(string $event, array $data) : void
    {
        $this->events[] = [
            'event' => $event,
            'data'  => $data,
            'time'  => microtime(true),
        ];
    }

    public function collect() : array
    {
        $snapshot = [];
        if (is_object($this->metrics) && method_exists($this->metrics, 'getSnapshot')) {
            /** @var array $snapshot */
            $snapshot = $this->metrics->getSnapshot();
        }

        $snapshot['events'] = $this->events;

        return $snapshot;
    }
}
