<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry\OpenTelemetry;

final readonly class OtelConfig
{
    public function __construct(
        public string $serviceName = 'avax-database',
        public bool $enabled = true,
        public array $attributes = [],
    ) {
    }
}
