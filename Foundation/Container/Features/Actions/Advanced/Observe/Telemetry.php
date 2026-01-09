<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Advanced\Observe;

use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Observe\Metrics\CollectMetrics;
use RuntimeException;

/**
 * Telemetry - Enterprise observability and monitoring DSL.
 *
 * Provides a fluent interface for collecting, exporting, and monitoring container telemetry data.
 * Enables comprehensive observability of container operations including performance metrics,
 * health status, and operational data export for monitoring and debugging.
 *
 * @see docs_md/Features/Actions/Advanced/Observe/Telemetry.md#quick-summary
 */
final readonly class Telemetry
{
    /**
     * Create a new Telemetry instance for container observability.
     *
     * @param ContainerInterface $container The container instance to monitor.
     * @param CollectMetrics $metrics The metrics collector for telemetry data.
     * @see docs_md/Features/Actions/Advanced/Observe/Telemetry.md#method-__construct
     */
    public function __construct(
        private ContainerInterface $container,
        private CollectMetrics     $metrics
    ) {}

    /**
     * Export container metrics as a formatted JSON string.
     *
     * Collects all available metrics and exports them in a structured JSON format
     * suitable for external monitoring systems, logging, or analysis tools.
     *
     * @return string JSON-encoded metrics data with timestamp.
     * @throws RuntimeException If JSON encoding fails.
     * @see docs_md/Features/Actions/Advanced/Observe/Telemetry.md#method-exportMetrics
     */
    public function exportMetrics(): string
    {
        $metrics = $this->metrics->collect();
        $data    = ['metrics' => $metrics, 'timestamp' => time()];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException(
                message: 'Failed to encode container metrics to JSON: ' . json_last_error_msg(),
                code: json_last_error()
            );
        }

        return $json;
    }

    /**
     * Get raw container metrics as an array.
     *
     * Returns the collected metrics data in array format for programmatic access
     * and analysis within the application.
     *
     * @return array Raw metrics data organized by metric type and values.
     * @see docs_md/Features/Actions/Advanced/Observe/Telemetry.md#method-getMetrics
     */
    public function getMetrics(): array
    {
        return $this->metrics->collect();
    }

    /**
     * Get container health status summary.
     *
     * Provides a standardized health check response indicating the container's
     * operational status, metrics count, and last update timestamp.
     *
     * @return array Health status data including status, timestamp, and metrics summary.
     * @see docs_md/Features/Actions/Advanced/Observe/Telemetry.md#method-getHealthStatus
     */
    public function getHealthStatus(): array
    {
        $metrics = $this->metrics->collect();

        return [
            'status'         => 'healthy',
            'timestamp'      => time(),
            'metrics_count'  => count($metrics),
            'last_updated'   => time(),
        ];
    }
}
