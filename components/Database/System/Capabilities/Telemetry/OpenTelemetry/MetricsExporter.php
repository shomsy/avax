<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Telemetry\OpenTelemetry;

use components\Database\System\Capabilities\Telemetry\DbMetricsCollector;

final class MetricsExporter
{
    /** @var list<array<string, mixed>> */
    private array $exported = [];

    public function export(DbMetricsCollector $collector, OtelConfig $config = new OtelConfig()) : void
    {
        if (! $config->enabled) {
            return;
        }

        $this->exported[] = $collector->snapshot() + [
                'service.name' => $config->serviceName,
                'attributes'   => $config->attributes,
            ];
    }

    public function exported() : array
    {
        return $this->exported;
    }
}
