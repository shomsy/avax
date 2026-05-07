<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\Flows\LivenessProbe;

use Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface\HealthCheck;
use Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface\HealthReport;

final readonly class LivenessProbe
{
    public function read() : HealthReport
    {
        return HealthCheck::liveness();
    }
}
