<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Config;

/**
 * Immutable configuration for container telemetry and observability.
 *
 * Controls how the container collects metrics, logs events, and interacts with
 * performance monitoring systems.
 *
 * @see     docs/Features/Operate/Config/TelemetryConfig.md
 */
readonly class TelemetryConfig
{
    /**
     * Initializes the telemetry configuration.
     *
     * @param bool  $enabled      Overall state of telemetry collection.
     * @param float $samplingRate Percentage of events to record (0.0 to 1.0).
     * @param bool  $reportErrors Whether to report internal container errors to telemetry.
     * @param bool  $trackCpu     Whether to include CPU usage in metrics.
     * @param bool  $trackMemory  Whether to include memory usage in metrics.
     *
     * @see docs/Features/Operate/Config/TelemetryConfig.md#method-__construct
     */
    public function __construct(
        public bool  $enabled = true,
        public float $samplingRate = 1.0,
        public bool  $reportErrors = true,
        public bool  $trackCpu = false,
        public bool  $trackMemory = true
    ) {}

    /**
     * Create a default production telemetry configuration.
     *
     * @return self Optimized for low overhead (sampled, no CPU tracking).
     *
     * @see docs/Features/Operate/Config/TelemetryConfig.md#method-production
     */
    public static function production() : self
    {
        return new self(
            enabled     : true,
            samplingRate: 0.1, // 10% sampling
            reportErrors: true,
            trackCpu    : false,
            trackMemory : true
        );
    }

    /**
     * Create a default development telemetry configuration.
     *
     * @return self Optimized for maximum visibility (full sampling, all metrics).
     *
     * @see docs/Features/Operate/Config/TelemetryConfig.md#method-development
     */
    public static function development() : self
    {
        return new self(
            enabled     : true,
            samplingRate: 1.0,
            reportErrors: true,
            trackCpu    : true,
            trackMemory : true
        );
    }

    /**
     * Create a default testing telemetry configuration.
     *
     * @return self Optimized for test environments (often disabled).
     *
     * @see docs/Features/Operate/Config/TelemetryConfig.md#method-testing
     */
    public static function testing() : self
    {
        return new self(
            enabled: false
        );
    }

    /**
     * Create a telemetry instance from a raw array.
     *
     * @param array<string, mixed> $data Configuration data.
     *
     * @return self Hydrated configuration instance.
     *
     * @see docs/Features/Operate/Config/TelemetryConfig.md#method-fromarray
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            enabled     : $data['enabled'] ?? true,
            samplingRate: (float) ($data['sampling_rate'] ?? 1.0),
            reportErrors: $data['report_errors'] ?? true,
            trackCpu    : $data['track_cpu'] ?? false,
            trackMemory : $data['track_memory'] ?? true
        );
    }
}
