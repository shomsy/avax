<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\WorkerManagement\PublicSurface;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Stringable;
use Avax\Framework\System\Capabilities\WorkerManagement\Capabilities\Workers\WorkerProcess;

final class WorkerPool
{
    /** @var list<WorkerProcess> */
    private array $workers = [];

    private bool $running = false;

    /**
     * @param array{max_memory?: int} $options
     */
    public function start(int $processes, array $options): void
    {
        $this->running = true;

        for ($i = 0; $i < $processes; $i++) {
            $this->workers[] = new WorkerProcess($options);
        }

        foreach ($this->workers as $worker) {
            $worker->start();
        }

        echo "Started {$processes} workers\n";

        $this->monitor();
    }

    private function monitor(): void
    {
        while ($this->running) {
            foreach ($this->workers as $worker) {
                if ($worker->isRunning() && $worker->isDead()) {
                    $worker->restart(true);
                }

                if ($worker->memoryUsage() > $worker->maxMemory()) {
                    $worker->restart(true);
                }
            }

            sleep(10);
        }
    }

    public function restart(bool $graceful): void
    {
        foreach ($this->workers as $worker) {
            $worker->restart($graceful);
        }

        echo "Workers restarted\n";
    }

    public function stop(): void
    {
        $this->running = false;

        foreach ($this->workers as $worker) {
            $worker->stop();
        }

        $this->workers = [];

        echo "All workers stopped\n";
    }

    public function drain(): void
    {
        foreach ($this->workers as $worker) {
            $worker->drain();
        }

        echo "Workers draining (no new tasks)\n";
    }

    public function status(): WorkerStatus
    {
        $running = 0;
        $idle = 0;
        $totalTasks = 0;
        $totalMemory = 0;

        foreach ($this->workers as $worker) {
            if ($worker->isRunning()) {
                $running++;
            }

            if ($worker->isIdle()) {
                $idle++;
            }

            $totalTasks += $worker->taskCount();
            $totalMemory += $worker->memoryUsage();
        }

        return new WorkerStatus(
            total: count($this->workers),
            running: $running,
            idle: $idle,
            totalTasks: $totalTasks,
            totalMemory: $totalMemory,
        );
    }
}
