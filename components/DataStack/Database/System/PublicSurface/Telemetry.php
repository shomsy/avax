<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Telemetry as TelemetryCapability;

/**
 * Public surface for Database Telemetry and Observability.
 */
final readonly class Telemetry
{
    public function __construct(
        private TelemetryCapability $telemetry,
    ) {}

    public function profile(callable $callback, string $label = 'database_operation') : mixed
    {
        return $this->telemetry->profile($callback, $label);
    }

    public function history() : array
    {
        return $this->telemetry->history();
    }
}
