<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Metrics;

/**
 * Router metrics collector with integrated alert thresholds.
 *
 * Collects comprehensive routing metrics and provides alert integration
 * for proactive monitoring of router performance and reliability.
 */
final class RouterMetricsCollector
{
    private array $metrics = [];
    private array $alertThresholds;

    public function __construct(array $alertConfig = [])
    {
        $this->alertThresholds = array_merge([
            'route_resolution_failures' => [
                'warning' => 10,    // failures per minute
                'critical' => 50,   // failures per minute
                'window' => 60,     // seconds
            ],
            'cache_invalidations' => [
                'warning' => 5,     // invalidations per minute
                'critical' => 20,   // invalidations per minute
                'window' => 60,     // seconds
            ],
            'route_resolution_time' => [
                'warning' => 100,   // milliseconds
                'critical' => 500,  // milliseconds
            ],
            'concurrent_requests' => [
                'warning' => 100,   // concurrent requests
                'critical' => 500,  // concurrent requests
            ],
        ], $alertConfig);
    }

    /**
     * Record a successful route resolution.
     */
    public function recordRouteResolution(
        string $method,
        string $path,
        float $durationMs,
        int $statusCode = 200
    ): void {
        $this->increment('route_resolutions_total', ['method' => $method, 'status' => $statusCode]);
        $this->observe('route_resolution_duration', $durationMs, ['method' => $method]);
        $this->setGauge('route_last_resolution_time', microtime(true));
    }

    /**
     * Record a route resolution failure.
     */
    public function recordRouteResolutionFailure(
        string $method,
        string $path,
        string $failureReason,
        float $durationMs
    ): void {
        $this->increment('route_resolution_failures_total', [
            'method' => $method,
            'reason' => $failureReason
        ]);
        $this->observe('route_resolution_duration', $durationMs, ['method' => $method, 'failed' => 'true']);
    }

    /**
     * Record cache operation metrics.
     */
    public function recordCacheOperation(
        string $operation, // 'hit', 'miss', 'write', 'invalidate'
        string $cacheType = 'routes',
        float $durationMs = 0.0
    ): void {
        $this->increment('cache_operations_total', [
            'operation' => $operation,
            'type' => $cacheType
        ]);

        if ($durationMs > 0) {
            $this->observe('cache_operation_duration', $durationMs, [
                'operation' => $operation,
                'type' => $cacheType
            ]);
        }

        if ($operation === 'invalidate') {
            $this->increment('cache_invalidations_total', ['type' => $cacheType]);
        }
    }

    /**
     * Record middleware execution metrics.
     */
    public function recordMiddlewareExecution(
        string $middlewareClass,
        float $durationMs,
        bool $passed = true
    ): void {
        $this->increment('middleware_executions_total', [
            'middleware' => $middlewareClass,
            'result' => $passed ? 'passed' : 'failed'
        ]);
        $this->observe('middleware_execution_duration', $durationMs, ['middleware' => $middlewareClass]);
    }

    /**
     * Record concurrent request metrics.
     */
    public function recordConcurrentRequests(int $count): void {
        $this->setGauge('concurrent_requests', $count);
    }

    /**
     * Get all current metrics for export.
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }

    /**
     * Export metrics in Prometheus format.
     */
    public function exportPrometheus(): string
    {
        $output = '';

        foreach ($this->metrics as $name => $metric) {
            $output .= $this->formatPrometheusMetric($name, $metric);
        }

        return $output;
    }

    /**
     * Check if any alert thresholds are exceeded.
     *
     * @return array<string, array{level: string, value: float|int, threshold: int|float, description: string}>
     */
    public function checkAlerts(): array
    {
        $alerts = [];

        // Check route resolution failures
        $failures = $this->getCounterValue('route_resolution_failures_total', 60); // per minute
        if ($failures >= $this->alertThresholds['route_resolution_failures']['critical']) {
            $alerts['route_resolution_failures'] = [
                'level' => 'critical',
                'value' => $failures,
                'threshold' => $this->alertThresholds['route_resolution_failures']['critical'],
                'description' => 'High rate of route resolution failures'
            ];
        } elseif ($failures >= $this->alertThresholds['route_resolution_failures']['warning']) {
            $alerts['route_resolution_failures'] = [
                'level' => 'warning',
                'value' => $failures,
                'threshold' => $this->alertThresholds['route_resolution_failures']['warning'],
                'description' => 'Elevated route resolution failures'
            ];
        }

        // Check cache invalidations
        $invalidations = $this->getCounterValue('cache_invalidations_total', 60);
        if ($invalidations >= $this->alertThresholds['cache_invalidations']['critical']) {
            $alerts['cache_invalidations'] = [
                'level' => 'critical',
                'value' => $invalidations,
                'threshold' => $this->alertThresholds['cache_invalidations']['critical'],
                'description' => 'High rate of cache invalidations'
            ];
        } elseif ($invalidations >= $this->alertThresholds['cache_invalidations']['warning']) {
            $alerts['cache_invalidations'] = [
                'level' => 'warning',
                'value' => $invalidations,
                'threshold' => $this->alertThresholds['cache_invalidations']['warning'],
                'description' => 'Elevated cache invalidations'
            ];
        }

        // Check route resolution time (95th percentile)
        $resolutionTime = $this->getPercentile('route_resolution_duration', 95);
        if ($resolutionTime >= $this->alertThresholds['route_resolution_time']['critical']) {
            $alerts['route_resolution_time'] = [
                'level' => 'critical',
                'value' => $resolutionTime,
                'threshold' => $this->alertThresholds['route_resolution_time']['critical'],
                'description' => 'Route resolution time too high'
            ];
        } elseif ($resolutionTime >= $this->alertThresholds['route_resolution_time']['warning']) {
            $alerts['route_resolution_time'] = [
                'level' => 'warning',
                'value' => $resolutionTime,
                'threshold' => $this->alertThresholds['route_resolution_time']['warning'],
                'description' => 'Route resolution time elevated'
            ];
        }

        // Check concurrent requests
        $concurrent = $this->getGaugeValue('concurrent_requests');
        if ($concurrent >= $this->alertThresholds['concurrent_requests']['critical']) {
            $alerts['concurrent_requests'] = [
                'level' => 'critical',
                'value' => $concurrent,
                'threshold' => $this->alertThresholds['concurrent_requests']['critical'],
                'description' => 'Too many concurrent requests'
            ];
        } elseif ($concurrent >= $this->alertThresholds['concurrent_requests']['warning']) {
            $alerts['concurrent_requests'] = [
                'level' => 'warning',
                'value' => $concurrent,
                'threshold' => $this->alertThresholds['concurrent_requests']['warning'],
                'description' => 'High concurrent request load'
            ];
        }

        return $alerts;
    }

    /**
     * Reset all metrics (for testing).
     */
    public function reset(): void {
        $this->metrics = [];
    }

    // Internal metric collection methods

    private function increment(string $name, array $labels = []): void {
        $key = $this->metricKey($name, $labels);
        $this->metrics[$key]['value'] = ($this->metrics[$key]['value'] ?? 0) + 1;
        $this->metrics[$key]['type'] = 'counter';
        $this->metrics[$key]['name'] = $name;
        $this->metrics[$key]['labels'] = $labels;
        $this->metrics[$key]['timestamp'] = microtime(true);
    }

    private function observe(string $name, float $value, array $labels = []): void {
        $key = $this->metricKey($name, $labels);

        if (!isset($this->metrics[$key]['values'])) {
            $this->metrics[$key]['values'] = [];
        }

        $this->metrics[$key]['values'][] = $value;
        $this->metrics[$key]['type'] = 'histogram';
        $this->metrics[$key]['name'] = $name;
        $this->metrics[$key]['labels'] = $labels;
    }

    private function setGauge(string $name, float $value, array $labels = []): void {
        $key = $this->metricKey($name, $labels);
        $this->metrics[$key]['value'] = $value;
        $this->metrics[$key]['type'] = 'gauge';
        $this->metrics[$key]['name'] = $name;
        $this->metrics[$key]['labels'] = $labels;
        $this->metrics[$key]['timestamp'] = microtime(true);
    }

    private function metricKey(string $name, array $labels): string {
        ksort($labels);
        return $name . '_' . md5(json_encode($labels));
    }

    private function formatPrometheusMetric(string $key, array $metric): string {
        $output = '';

        $labels = '';
        if (!empty($metric['labels'])) {
            $labelParts = [];
            foreach ($metric['labels'] as $k => $v) {
                $labelParts[] = $k . '="' . addslashes((string)$v) . '"';
            }
            $labels = '{' . implode(',', $labelParts) . '}';
        }

        if ($metric['type'] === 'counter') {
            $output .= "# HELP {$metric['name']} Counter metric\n";
            $output .= "# TYPE {$metric['name']} counter\n";
            $output .= "{$metric['name']}{$labels} {$metric['value']}\n";
        } elseif ($metric['type'] === 'gauge') {
            $output .= "# HELP {$metric['name']} Gauge metric\n";
            $output .= "# TYPE {$metric['name']} gauge\n";
            $output .= "{$metric['name']}{$labels} {$metric['value']}\n";
        } elseif ($metric['type'] === 'histogram') {
            $output .= "# HELP {$metric['name']} Histogram metric\n";
            $output .= "# TYPE {$metric['name']} histogram\n";

            // Calculate percentiles
            if (!empty($metric['values'])) {
                sort($metric['values']);
                $count = count($metric['values']);
                $p50 = $metric['values'][(int)($count * 0.5)] ?? 0;
                $p95 = $metric['values'][(int)($count * 0.95)] ?? 0;
                $p99 = $metric['values'][(int)($count * 0.99)] ?? 0;

                $output .= "{$metric['name']}_count{$labels} {$count}\n";
                $output .= "{$metric['name']}{quantile=\"0.5\"{$labels}} {$p50}\n";
                $output .= "{$metric['name']}{quantile=\"0.95\"{$labels}} {$p95}\n";
                $output .= "{$metric['name']}{quantile=\"0.99\"{$labels}} {$p99}\n";
            }
        }

        return $output;
    }

    private function getCounterValue(string $name, int $windowSeconds = 60): int {
        $total = 0;
        $cutoff = microtime(true) - $windowSeconds;

        foreach ($this->metrics as $metric) {
            if (($metric['name'] ?? '') === $name &&
                ($metric['timestamp'] ?? 0) >= $cutoff) {
                $total += $metric['value'] ?? 0;
            }
        }

        return $total;
    }

    private function getPercentile(string $name, float $percentile): float {
        $values = [];

        foreach ($this->metrics as $metric) {
            if (($metric['name'] ?? '') === $name && isset($metric['values'])) {
                $values = array_merge($values, $metric['values']);
            }
        }

        if (empty($values)) {
            return 0.0;
        }

        sort($values);
        $index = (int)(count($values) * ($percentile / 100));

        return $values[$index] ?? end($values);
    }

    private function getGaugeValue(string $name): float {
        foreach ($this->metrics as $metric) {
            if (($metric['name'] ?? '') === $name && $metric['type'] === 'gauge') {
                return $metric['value'] ?? 0.0;
            }
        }

        return 0.0;
    }
}