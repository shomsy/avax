<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\RunThroughProcessPool;

use Symfony\Component\Process\Process;

final readonly class StopWorkerProcess
{
    public function forceKill(Process $process) : void
    {
        if ($process->isRunning()) {
            $process->stop(0);
        }
    }

    public function isRunning(Process $process) : bool
    {
        return $process->isRunning();
    }

    public function stop(Process $process, int $timeoutSeconds = 5) : bool
    {
        if ($process->isRunning()) {
            $process->stop($timeoutSeconds);
        }

        return ! $process->isRunning();
    }

    public function getPid(Process $process) : ?int
    {
        return $process->getPid();
    }
}
