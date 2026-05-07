<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Flows\ExportTelemetry;

use Avax\Components\Operations\Observability\System\Capabilities\Drivers\TelemetryExporter;

final readonly class ExportTelemetry
{
    /**
     * @param array<string, mixed> $data
     */
    public function export(TelemetryExporter $exporter, array $data) : bool
    {
        return $exporter->export($data);
    }
}
