<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\System\Flows\ReadinessProbe;

use Avax\Components\DeveloperTools\Diagnostics\System\System\PublicSurface\HealthCheck;
use Avax\Components\DeveloperTools\Diagnostics\System\System\PublicSurface\HealthReport;

final readonly class ReadinessProbe
{
    public function read() : HealthReport
    {
        return HealthCheck::readiness();
    }
}
