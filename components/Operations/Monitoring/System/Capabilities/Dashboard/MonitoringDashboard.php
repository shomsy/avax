<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Monitoring\System\Capabilities\Dashboard;

use Avax\Components\Operations\Monitoring\System\Capabilities\Health\HealthReport;
use Avax\Components\Operations\Monitoring\System\Capabilities\Metrics\MetricsRegistry;

final readonly class MonitoringDashboard
{
    public function __construct(
        private MetricsRegistry $metrics,
        private HealthReport    $health,
    ) {}

    public function data() : array
    {
        return [
            'health'       => $this->health->toArray(),
            'metrics'      => $this->metrics->snapshot(),
            'generated_at' => date(format: DATE_ATOM),
        ];
    }
}
