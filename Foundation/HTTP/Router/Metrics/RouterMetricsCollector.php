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
    private array $metrics = [] {
        get {
            return $this->metrics;
        }
    }
    private array $alertThresholds;

    public function __construct(array $alertConfig = [])
    {
        $this->alertThresholds = array_merge([
                                                 'route_resolution_failures' => [
                                                     'warning' => 10,    // failures per minute
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                'critical' => 50,   // failures per minute
                                                     'window'  => 60,     // seconds
                                                 ],
                                                 'cache_invalidations'       => [
                                                     'warning' => 5,     // invalidations per minute
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                'critical' => 20,   // invalidations per minute
                                                     'window'  => 60,     // seconds
                                                 ],
                                                 'route_resolution_time'     => [
                                                     'warning' => 100,   // milliseconds
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                'critical' => 500,  // milliseconds
                                                 ],
                                                 'concurrent_requests'       => [
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
        float  $durationMs,
        int    $statusCode = 200
    ) : void
    {
        $this->increment(name: 'route_resolutions_total', labels: ['method' => $method, 'status' => $statusCode]);
        $this->observe(name: 'route_resolution_duration', value: $durationMs, labels: ['method' => $method]);
        $this->setGauge(name: 'route_last_resolution_time', value: microtime(true));
    }

    private function increment(string $name, array $labels = []) : void
    {
        $key                              = $this->metricKey(name: $name, labels: $labels);
        $this->metrics[$key]['value']     = ($this->metrics[$key]['value'] ?? 0) + 1;
        $this->metrics[$key]['type']      = 'counter';
        $this->metrics[$key]['name']      = $name;
        $this->metrics[$key]['labels']    = $labels;
        $this->metrics[$key]['timestamp'] = microtime(true);
    }

    private function metricKey(string $name, array $labels) : string
    {
        ksort($labels);

        return $name . '_' . md5(json_encode($labels));
    }

    private function observe(string $name, float $value, array $labels = []) : void
    {
        $key = $this->metricKey(name: $name, labels: $labels);

        if (! isset($this->metrics[$key]['values'])) {
            $this->metrics[$key]['values'] = [];
        }

        $this->metrics[$key]['values'][] = $value;
        $this->metrics[$key]['type']     = 'histogram';
        $this->metrics[$key]['name']     = $name;
        $this->metrics[$key]['labels']   = $labels;
    }

    private function setGauge(string $name, float $value) : void
    {
        $labels                           = [];
        $key                              = $this->metricKey(name: $name, labels: $labels);
        $this->metrics[$key]['value']     = $value;
        $this->metrics[$key]['type']      = 'gauge';
        $this->metrics[$key]['name']      = $name;
        $this->metrics[$key]['labels']    = $labels;
        $this->metrics[$key]['timestamp'] = microtime(true);
    }

    /**
     * Record a route resolution failure.
     */
    public function recordRouteResolutionFailure(
        string $method,
        string $path,
        string $failureReason,
        float  $durationMs
    ) : void
    {
        $this->increment(name: 'route_resolution_failures_total', labels: [
            'method' => $method,
            'reason' => $failureReason
        ]);
        $this->observe(name: 'route_resolution_duration', value: $durationMs, labels: ['method' => $method, 'failed' => 'true']);
    }

    /**
     * Record cache operation metrics.
     */
    public function recordCacheOperation(
        string      $operation, // 'hit', 'miss', 'write', 'invalidate'
        string|null $cacheType = null,
        float       $durationMs = 0.0
    ) : void
    {
        $cacheType ??= 'routes';
        $this->increment(name: 'cache_operations_total', labels: [
            'operation' => $operation,
            'type'      => $cacheType
        ]);

        if ($durationMs > 0) {
            $this->observe(name: 'cache_operation_duration', value: $durationMs, labels: [
                'operation' => $operation,
                'type'      => $cacheType
            ]);
        }

        if ($operation === 'invalidate') {
            $this->increment(name: 'cache_invalidations_total', labels: ['type' => $cacheType]);
        }
    }

    /**
     * Record middleware execution metrics.
     */
    public function recordMiddlewareExecution(
        string $middlewareClass,
        float  $durationMs,
        bool   $passed = true
    ) : void
    {
        $this->increment(name: 'middleware_executions_total', labels: [
            'middleware' => $middlewareClass,
            'result'     => $passed ? 'passed' : 'failed'
        ]);
        $this->observe(name: 'middleware_execution_duration', value: $durationMs, labels: ['middleware' => $middlewareClass]);
    }

    /**
     * Record concurrent request metrics.
     */
    public function recordConcurrentRequests(int $count) : void
    {
        $this->setGauge(name: 'concurrent_requests', value: $count);
    }

    // Internal metric collection methods

    /**
     * Export metrics in Prometheus format.
     */
    public function exportPrometheus() : string
    {
        $output = '';

        foreach ($this->metrics as $name => $metric) {
            $output .= $this->formatPrometheusMetric(key: $name, metric: $metric);
        }

        return $output;
    }

    private function formatPrometheusMetric(string $key, array $metric) : string
    {
        $output = '';

        $labels = '';
        if (! empty($metric['labels'])) {
            $labelParts = [];
            foreach ($metric['labels'] as $k => $v) {
                $labelParts[] = $k . '="' . addslashes((string) $v) . '"';
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
            if (! empty($metric['values'])) {
                sort($metric['values']);
                $count = count($metric['values']);
                $p50   = $metric['values'][(int) ($count * 0.5)] ?? 0;
                $p95   = $metric['values'][(int) ($count * 0.95)] ?? 0;
                $p99   = $metric['values'][(int) ($count * 0.99)] ?? 0;

                $output .= "{$metric['name']}_count{$labels} {$count}\n";
                $output .= "{$metric['name']}{quantile=\"0.5\"{$labels}} {$p50}\n";
                $output .= "{$metric['name']}{quantile=\"0.95\"{$labels}} {$p95}\n";
                $output .= "{$metric['name']}{quantile=\"0.99\"{$labels}} {$p99}\n";
            }
        }

        return $output;
    }

    /**
     * Check if any alert thresholds are exceeded.
     *
     * @return array<string, array{level: string, value: float|int, threshold: int|float, description: string}>
     */
    public function checkAlerts() : array
    {
        $alerts = [];

        // Check route resolution failures
        $failures = $this->getCounterValue(name: 'route_resolution_failures_total'); // per minute
        if ($failures >= $this->alertThresholds['route_resolution_failures']['critical']) {
            $alerts['route_resolution_failures'] = [
                'level'       => 'critical',
                'value'       => $failures,
                'threshold'   => $this->alertThresholds['route_resolution_failures']['critical'],
                'description' => 'High rate of route resolution failures'
            ];
        } elseif ($failures >= $this->alertThresholds['route_resolution_failures']['warning']) {
            $alerts['route_resolution_failures'] = [
                'level'       => 'warning',
                'value'       => $failures,
                'threshold'   => $this->alertThresholds['route_resolution_failures']['warning'],
                'description' => 'Elevated route resolution failures'
            ];
        }

        // Check cache invalidations
        $invalidations = $this->getCounterValue(name: 'cache_invalidations_total');
        if ($invalidations >= $this->alertThresholds['cache_invalidations']['critical']) {
            $alerts['cache_invalidations'] = [
                'level'       => 'critical',
                'value'       => $invalidations,
                'threshold'   => $this->alertThresholds['cache_invalidations']['critical'],
                'description' => 'High rate of cache invalidations'
            ];
        } elseif ($invalidations >= $this->alertThresholds['cache_invalidations']['warning']) {
            $alerts['cache_invalidations'] = [
                'level'       => 'warning',
                'value'       => $invalidations,
                'threshold'   => $this->alertThresholds['cache_invalidations']['warning'],
                'description' => 'Elevated cache invalidations'
            ];
        }

        // Check route resolution time (95th percentile)
        $resolutionTime = $this->getPercentile();
        if ($resolutionTime >= $this->alertThresholds['route_resolution_time']['critical']) {
            $alerts['route_resolution_time'] = [
                'level'       => 'critical',
                'value'       => $resolutionTime,
                'threshold'   => $this->alertThresholds['route_resolution_time']['critical'],
                'description' => 'Route resolution time too high'
            ];
        } elseif ($resolutionTime >= $this->alertThresholds['route_resolution_time']['warning']) {
            $alerts['route_resolution_time'] = [
                'level'       => 'warning',
                'value'       => $resolutionTime,
                'threshold'   => $this->alertThresholds['route_resolution_time']['warning'],
                'description' => 'Route resolution time elevated'
            ];
        }

        // Check concurrent requests
        $concurrent = $this->getGaugeValue();
        if ($concurrent >= $this->alertThresholds['concurrent_requests']['critical']) {
            $alerts['concurrent_requests'] = [
                'level'       => 'critical',
                'value'       => $concurrent,
                'threshold'   => $this->alertThresholds['concurrent_requests']['critical'],
                'description' => 'Too many concurrent requests'
            ];
        } elseif ($concurrent >= $this->alertThresholds['concurrent_requests']['warning']) {
            $alerts['concurrent_requests'] = [
                'level'       => 'warning',
                'value'       => $concurrent,
                'threshold'   => $this->alertThresholds['concurrent_requests']['warning'],
                'description' => 'High concurrent request load'
            ];
        }

        return $alerts;
    }

    private function getCounterValue(string $name) : int
    {
        $total  = 0;
        $cutoff = microtime(true) - 60;

        foreach ($this->metrics as $metric) {
            if (($metric['name'] ?? '') === $name &&
                ($metric['timestamp'] ?? 0) >= $cutoff) {
                $total += $metric['value'] ?? 0;
            }
        }

        return $total;
    }

    private function getPercentile() : float
    {
        $values = [];

        foreach ($this->metrics as $metric) {
            if (($metric['name'] ?? '') === 'route_resolution_duration' && isset($metric['values'])) {
                $values = array_merge($values, $metric['values']);
            }
        }

        if (empty($values)) {
            return 0.0;
        }

        sort($values);
        $index = (int) (count($values) * (95 / 100));

        return $values[$index] ?? end($values);
    }

    private function getGaugeValue() : float
    {
        foreach ($this->metrics as $metric) {
            if (($metric['name'] ?? '') === 'concurrent_requests' && $metric['type'] === 'gauge') {
                return $metric['value'] ?? 0.0;
            }
        }

        return 0.0;
    }

    /**
     * Reset all metrics (for testing).
     */
    public function reset() : void
    {
        $this->metrics = [];
    }
}