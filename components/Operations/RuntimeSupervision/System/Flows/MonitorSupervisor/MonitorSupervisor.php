<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Flows\MonitorSupervisor;

use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Supervision\Supervisor;

final readonly class MonitorSupervisor
{
    /**
     * @return array<string, mixed>
     */
    public function monitor(Supervisor $supervisor) : array
    {
        return $supervisor->monitor();
    }
}
