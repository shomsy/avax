<?php

declare(strict_types=1);

namespace Foundation\HTTP\Session\Observability;

/**
 * MetricCollector
 *
 * Simple in-memory metrics collector for Prometheus-compatible exporters.
 *
 * @package Foundation\HTTP\Session\Observability
 */
final class MetricCollector
{
    private array $metrics = [
        'session_ops_total' => 0,
        'session_errors_total' => 0,
        'session_latency_seconds' => []
    ];

    public function increment(string $metric): void
    {
        if (!isset($this->metrics[$metric])) {
            $this->metrics[$metric] = 0;
        }
        $this->metrics[$metric]++;
    }

    public function observeLatency(float $seconds): void
    {
        $this->metrics['session_latency_seconds'][] = $seconds;
    }

    public function export(): array
    {
        $avgLatency = empty($this->metrics['session_latency_seconds'])
            ? 0
            : array_sum($this->metrics['session_latency_seconds']) / count($this->metrics['session_latency_seconds']);

        return [
            'session_ops_total' => $this->metrics['session_ops_total'],
            'session_errors_total' => $this->metrics['session_errors_total'],
            'session_latency_avg_seconds' => $avgLatency,
        ];
    }
}
