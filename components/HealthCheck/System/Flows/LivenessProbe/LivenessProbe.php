<?php

declare(strict_types=1);

namespace Avax\Components\HealthCheck\System\Flows\LivenessProbe;

use Avax\Components\HealthCheck\System\PublicSurface\HealthCheck;
use Avax\Components\HealthCheck\System\PublicSurface\HealthReport;

final readonly class LivenessProbe
{
    public function read() : HealthReport
    {
        return HealthCheck::liveness();
    }
}
