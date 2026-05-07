<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Flows\StopSupervisor;

use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Supervision\Supervisor;

final readonly class StopSupervisor
{
    public function stop(Supervisor $supervisor, string $processId) : void
    {
        $supervisor->stop($processId);
    }
}
