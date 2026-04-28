<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Telemetry\OpenTelemetry;

final class OtelConfig
{
    public function __construct(
        public readonly string $serviceName = 'avax-database',
        public readonly bool   $enabled = true,
        public readonly array  $attributes = [],
    ) {}
}
