<?php

declare(strict_types=1);
namespace Avax\Container\Features\Operate\Config;

/**
 * Configuration for container telemetry and metrics collection.
 *
 * Controls how the container collects and reports performance metrics,
 * resolution timelines, and diagnostic information.
 *
 * @see docs_md/Features/Operate/Config/TelemetryConfig.md#quick-summary
 */
final readonly class TelemetryConfig
{
    /**
     * @param bool   $enabled            Enable telemetry collection
     * @param string $sink               Telemetry sink type ('null', 'json', 'psr')
     * @param string $outputPath         File path for JSON output (when using 'json' sink)
     * @param int    $sampleRate         Sampling rate (1 = every request, 10 = every 10th)
     * @param bool   $includeStackTraces Include stack traces in diagnostics
     * @param array  $trackedEvents      List of events to track
     * @see docs_md/Features/Operate/Config/TelemetryConfig.md#method-__construct
     */
    public function __construct(
        public bool   $enabled = false,
        public string $sink = 'null',
        public string $outputPath = '',
        public int    $sampleRate = 1,
        public bool   $includeStackTraces = false,
        public array  $trackedEvents = ['resolve', 'inject', 'cache_hit', 'cache_miss'],
    ) {}

    /**
     * Create config from array.
     *
     * @see docs_md/Features/Operate/Config/TelemetryConfig.md#method-fromarray
     */
    public static function fromArray(array $config) : self
    {
        return new self(
            enabled           : $config['enabled'] ?? false,
            sink              : $config['sink'] ?? 'null',
            outputPath        : $config['outputPath'] ?? '',
            sampleRate        : $config['sampleRate'] ?? 1,
            includeStackTraces: $config['includeStackTraces'] ?? false,
            trackedEvents     : $config['trackedEvents'] ?? ['resolve', 'inject', 'cache_hit', 'cache_miss'],
        );
    }

    /**
     * Development telemetry preset.
     *
     * @see docs_md/Features/Operate/Config/TelemetryConfig.md#method-development
     */
    public static function development() : self
    {
        return new self(
            enabled           : true,
            sink              : 'json',
            outputPath        : sys_get_temp_dir() . '/container-telemetry.json',
            sampleRate        : 1,
            includeStackTraces: true,
            trackedEvents     : ['resolve', 'inject', 'cache_hit', 'cache_miss', 'error'],
        );
    }

    /**
     * Production telemetry preset.
     *
     * @see docs_md/Features/Operate/Config/TelemetryConfig.md#method-production
     */
    public static function production() : self
    {
        return new self(
            enabled           : true,
            sink              : 'psr',
            outputPath        : '',
            sampleRate        : 100,
            includeStackTraces: false,
            trackedEvents     : ['resolve', 'cache_hit', 'error'],
        );
    }
}
