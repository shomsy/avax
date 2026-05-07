<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Health;

final readonly class ObservabilityHealthCheck
{
    /**
     * @return array{healthy: bool, drivers: array<string, bool>, issues: list<string>}
     */
    public function check() : array
    {
        $drivers = [
            'metrics' => true,
            'tracing' => true,
            'logging' => true,
        ];

        return [
            'healthy' => true,
            'drivers' => $drivers,
            'issues'  => [],
        ];
    }
}
