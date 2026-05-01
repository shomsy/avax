<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry\OpenTelemetry;

final class TraceExporter
{
    /** @var list<array<string, mixed>> */
    private array $exported = [];

    public function export(QuerySpan $querySpan, OtelConfig $otelConfig = new OtelConfig()) : void
    {
        if (! $otelConfig->enabled) {
            return;
        }

        $this->exported[] = $querySpan->toArray() + [
                'service.name' => $otelConfig->serviceName,
                'attributes'   => $otelConfig->attributes,
        ];
    }

    public function exported(): array
    {
        return $this->exported;
    }
}
