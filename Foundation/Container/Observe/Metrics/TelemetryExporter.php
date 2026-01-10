<?php

declare(strict_types=1);
namespace Avax\Container\Observe\Metrics;

/**
 * Telemetry data exporter interface for metrics collection.
 *
 * This interface defines the contract for exporting telemetry data
 * collected during container operations. Implementations can send
 * metrics to various monitoring systems, logging frameworks, or
 * analytics platforms.
 *
 * ARCHITECTURAL ROLE:
 * - Defines telemetry export contract for loose coupling
 * - Enables pluggable monitoring and analytics backends
 * - Supports both counters and observations (gauges/histograms)
 *
 * EXPORTED METRICS:
 * - Counters: Monotonically increasing values (e.g., request counts)
 * - Observations: Point-in-time measurements (e.g., response times)
 *
 * USAGE SCENARIOS:
 * ```php
 * $exporter = new MonitoringExporter();
 * $exporter->increment('requests_total');
 * $exporter->observe('response_time', 0.142);
 * ```
 *
 * IMPLEMENTATION NOTES:
 * - increment() for counting events
 * - observe() for measuring values
 * - Thread-safe implementations recommended
 * - Asynchronous export preferred for performance
 *
 * @package Avax\Container\Observe\Metrics
 * @see docs/Observe/Metrics/TelemetryExporter.md#quick-summary
 */
interface TelemetryExporter
{
    /**
     * Increment a counter metric.
     *
     * Increases the value of a counter by the specified amount.
     * Counters should only increase and represent cumulative counts.
     *
     * @param string $metric The metric name/identifier
     * @param int    $value  The increment value (default: 1)
     *
     * @return void
     * @see docs/Observe/Metrics/TelemetryExporter.md#method-increment
     */
    public function increment(string $metric, int $value = 1) : void;

    /**
     * Record an observation metric.
     *
     * Records a single observation value for a gauge or histogram metric.
     * Multiple calls represent independent measurements.
     *
     * @param string $metric The metric name/identifier
     * @param float  $value  The observed value
     *
     * @return void
     * @see docs/Observe/Metrics/TelemetryExporter.md#method-observe
     */
    public function observe(string $metric, float $value) : void;
}
