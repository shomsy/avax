<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Observability\Telemetry;

use Avax\Container\DependencyInjection\Capabilities\Observability\Metrics\CollectMetrics;
use RuntimeException;

/**
 * Read-only observability facade over collected metrics.
 */
final readonly class Telemetry
{
    public function __construct(
        private CollectMetrics|null $metrics = null
    ) {}

    public function exportMetrics() : string
    {
        $data = [
            'metrics'   => $this->collector()->collect(),
            'timestamp' => time(),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException(
                message: 'Failed to encode container metrics to JSON: ' . json_last_error_msg(),
                code   : json_last_error()
            );
        }

        return $json;
    }

    public function getMetrics() : array
    {
        return $this->collector()->collect();
    }

    public function getHealthStatus() : array
    {
        $metrics = $this->collector()->collect();

        return [
            'status'        => 'healthy',
            'timestamp'     => time(),
            'metrics_count' => count($metrics),
            'last_updated'  => time(),
        ];
    }

    private function collector() : CollectMetrics
    {
        return $this->metrics ?? new CollectMetrics;
    }
}
