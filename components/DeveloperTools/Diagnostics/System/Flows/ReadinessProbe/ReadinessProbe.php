<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\HealthCheck\System\Flows\ReadinessProbe;

use Avax\Components\Operations\Observability\System\Capabilities\HealthCheck\System\PublicSurface\HealthCheck;
use Avax\Components\Operations\Observability\System\Capabilities\HealthCheck\System\PublicSurface\HealthReport;

final readonly class ReadinessProbe
{
    public function read() : HealthReport
    {
        return HealthCheck::readiness();
    }
}
