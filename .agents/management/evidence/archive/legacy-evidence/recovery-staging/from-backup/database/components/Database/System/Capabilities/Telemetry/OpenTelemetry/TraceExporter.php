<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Telemetry\OpenTelemetry;

final class TraceExporter
{
    /** @var list<array<string, mixed>> */
    private array $exported = [];

    public function export(QuerySpan $span, OtelConfig $config = new OtelConfig()): void
    {
        if (! $config->enabled) {
            return;
        }

        $this->exported[] = $span->toArray() + [
            'service.name' => $config->serviceName,
            'attributes' => $config->attributes,
        ];
    }

    public function exported(): array
    {
        return $this->exported;
    }
}
