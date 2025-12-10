<?php

declare(strict_types=1);

namespace Avax\Foundation\HTTP\Session\Observability;

/**
 * PrometheusExporter
 *
 * Exports session metrics in Prometheus text-based exposition format.
 *
 * @package Foundation\HTTP\Session\Observability
 */
final readonly class PrometheusExporter
{
    public function __construct(
        private MetricCollector $collector
    ) {}

    public function render() : string
    {
        $metrics = $this->collector->export();
        $lines   = [];

        foreach ($metrics as $name => $value) {
            $lines[] = sprintf('# TYPE %s gauge', $name);
            $lines[] = sprintf('%s %s', $name, $value);
        }

        return implode(separator: PHP_EOL, array: $lines) . PHP_EOL;
    }
}
