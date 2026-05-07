<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Flows\StartSupervisor;

use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Process\ProcessRecord;
use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Supervision\Supervisor;

final readonly class StartSupervisor
{
    public function start(Supervisor $supervisor, string $processId, string $name) : ProcessRecord
    {
        $process = new ProcessRecord(
            id       : $processId,
            name     : $name,
            status   : 'starting',
            pid      : 0,
            startedAt: hrtime(true) / 1e9,
        );

        $supervisor->start($process);

        return new ProcessRecord(
            id       : $processId,
            name     : $name,
            status   : 'running',
            pid      : 0,
            startedAt: hrtime(true) / 1e9,
        );
    }
}
