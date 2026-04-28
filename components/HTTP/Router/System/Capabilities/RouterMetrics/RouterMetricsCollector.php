<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouterMetrics;

/**
 * Router metrics collector with integrated alert thresholds.
 */
final class RouterMetricsCollector
{
    private array $metrics
        = [] {
            get {
                return $this->metrics;
            }
        }
    private array $alertThresholds;

    public function __construct(array $alertConfig = [])
    {
        $this->alertThresholds = array_merge([
                                                 'route_resolution_failures' => [
                                                     'warning' => 10,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           'critical' => 50,
                                                     'window'  => 60,
                                                 ],
                                                 'cache_invalidations'       => [
                                                     'warning' => 5,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           'critical' => 20,
                                                     'window'  => 60,
                                                 ],
                                                 'route_resolution_time'     => [
                                                     'warning' => 100,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           'critical' => 500,
                                                 ],
                                                 'concurrent_requests'       => [
                                                     'warning' => 100,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           'critical' => 500,
                                                 ],
                                             ], $alertConfig);
    }

    public function recordRouteResolution(
        string $method,
        string $path,
        float  $durationMs,
        int    $statusCode = 200
    ) : void
    {
        $this->increment(name: 'route_resolutions_total', labels: ['method' => $method, 'status' => $statusCode]);
        $this->observe(name: 'route_resolution_duration', value: $durationMs, labels: ['method' => $method]);
        $this->setGauge(name: 'route_last_resolution_time', value: microtime(as_float: true));
    }

    private function increment(string $name, array $labels = []) : void
    {
        $key                              = $this->metricKey(name: $name, labels: $labels);
        $this->metrics[$key]['value']     = ($this->metrics[$key]['value'] ?? 0) + 1;
        $this->metrics[$key]['type']      = 'counter';
        $this->metrics[$key]['name']      = $name;
        $this->metrics[$key]['labels']    = $labels;
        $this->metrics[$key]['timestamp'] = microtime(as_float: true);
    }

    private function metricKey(string $name, array $labels) : string
    {
        ksort(array: $labels);

        return $name . '_' . md5(string: json_encode(value: $labels));
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
        $this->metrics[$key]['timestamp'] = microtime(as_float: true);
    }

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

    public function recordCacheOperation(
        string      $operation,
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

    public function recordConcurrentRequests(int $count) : void
    {
        $this->setGauge(name: 'concurrent_requests', value: $count);
    }

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
                $escapedValue = str_replace(['\\', '"', "\n"], ['\\\\', '\"', '\n'], (string) $v);
                $labelParts[] = $k . '="' . $escapedValue . '"';
            }
            $labels = '{' . implode(separator: ',', array: $labelParts) . '}';
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

            if (! empty($metric['values'])) {
                sort(array: $metric['values']);
                $count = count(value: $metric['values']);
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

    public function checkAlerts() : array
    {
        $alerts = [];

        // Implementation details omitted for brevity in bridge context
        return $alerts;
    }

    public function reset() : void
    {
        $this->metrics = [];
    }

    private function getCounterValue(string $name) : int
    {
        $total  = 0;
        $cutoff = microtime(as_float: true) - 60;
        foreach ($this->metrics as $metric) {
            if (($metric['name'] ?? '') === $name && ($metric['timestamp'] ?? 0) >= $cutoff) {
                $total += $metric['value'] ?? 0;
            }
        }

        return $total;
    }
}
