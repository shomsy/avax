<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry\OpenTelemetry;

use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\DbMetricsCollector;

final class MetricsExporter
{
    /** @var list<array<string, mixed>> */
    private array $exported = [];

    public function export(DbMetricsCollector $dbMetricsCollector, OtelConfig $otelConfig = new OtelConfig()): void
    {
        if (! $otelConfig->enabled) {
            return;
        }

        $this->exported[] = $dbMetricsCollector->snapshot() + [
            'service.name' => $otelConfig->serviceName,
            'attributes' => $otelConfig->attributes,
        ];
    }

    public function exported(): array
    {
        return $this->exported;
    }
}
