<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Flows\RestartWorker;

use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Process\ProcessRecord;
use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Supervision\Supervisor;
use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\WorkerRestart\WorkerRestartPolicy;

final readonly class RestartWorker
{
    public function restart(Supervisor $supervisor, WorkerRestartPolicy $policy, string $workerId, string $name, int $restartCount, int $failures) : array
    {
        if (! $policy->shouldRestart($restartCount, $failures)) {
            return [
                'restarted' => false,
                'reason'    => 'Restart policy prevents restart.',
            ];
        }

        $supervisor->stop($workerId);
        $supervisor->start(new ProcessRecord(
                               id       : $workerId,
                               name     : $name,
                               status   : 'starting',
                               pid      : 0,
                               startedAt: hrtime(true) / 1e9,
                           ));

        return [
            'restarted' => true,
            'backoff'   => $policy->backoffDelay($restartCount),
        ];
    }
}
