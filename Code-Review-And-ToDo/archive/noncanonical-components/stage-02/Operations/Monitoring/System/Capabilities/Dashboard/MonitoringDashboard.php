<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Monitoring\System\Capabilities\Dashboard;

use Avax\Components\Operations\Monitoring\System\Capabilities\Health\HealthReport;
use Avax\Components\Operations\Monitoring\System\Capabilities\Metrics\MetricsRegistry;

final readonly class MonitoringDashboard
{
    public function __construct(
        private MetricsRegistry $metricsRegistry,
        private HealthReport $healthReport,
    ) {
    }

    public function data(): array
    {
        return [
            'health' => $this->healthReport->toArray(),
            'metrics' => $this->metricsRegistry->snapshot(),
            'generated_at' => date(format: DATE_ATOM),
        ];
    }
}
