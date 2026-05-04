<?php

declare(strict_types=1);

namespace Avax\Components\ystem\Capabilities\WorkerManagement\System\PublicSurface;

use Avax\Components\WorkerManager\System\Capabilities\Workers\WorkerProcess;
use Stringable;

final readonly class WorkerPool
{
    public function __construct(private WorkerPool $workerPool) {}

    /**
     * @param array{max_memory?: int} $options
     */
    public function start(int $processes = 1, array $options = []) : void
    {
        $this->workerPool->start($processes, $options);
    }

    public function stop() : void
    {
        $this->workerPool->stop();
    }

    public function restart(bool $graceful = true) : void
    {
        $this->workerPool->restart($graceful);
    }

    public function status() : WorkerStatus
    {
        return $this->workerPool->status();
    }

    public function drain() : void
    {
        $this->workerPool->drain();
    }
}

final class WorkerPool
{
    /** @var list<WorkerProcess> */
    private array $workers = [];

    private bool $running = false;

    /**
     * @param array{max_memory?: int} $options
     */
    public function start(int $processes, array $options) : void
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

    private function monitor() : void
    {
        while ( $this->running ) {
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

    public function restart(bool $graceful) : void
    {
        foreach ($this->workers as $worker) {
            $worker->restart($graceful);
        }

        echo "Workers restarted\n";
    }

    public function stop() : void
    {
        $this->running = false;

        foreach ($this->workers as $worker) {
            $worker->stop();
        }

        $this->workers = [];

        echo "All workers stopped\n";
    }

    public function drain() : void
    {
        foreach ($this->workers as $worker) {
            $worker->drain();
        }

        echo "Workers draining (no new tasks)\n";
    }

    public function status() : WorkerStatus
    {
        $running    = 0;
        $idle       = 0;
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
            total      : count($this->workers),
            running    : $running,
            idle       : $idle,
            totalTasks : $totalTasks,
            totalMemory: $totalMemory,
        );
    }
}

final readonly class WorkerStatus implements Stringable
{
    public function __construct(
        public int $total,
        public int $running,
        public int $idle,
        public int $totalTasks,
        public int $totalMemory,
    ) {}

    /**
     * @return array{total: int, running: int, idle: int, total_tasks: int, total_memory_mb: int}
     */
    public function toArray() : array
    {
        return [
            'total'       => $this->total,
            'running'     => $this->running,
            'idle'        => $this->idle,
            'total_tasks' => $this->totalTasks,
            'total_memory_mb' => $this->totalMemory,
        ];
    }

    public function __toString() : string
    {
        return sprintf('Workers: %d total, %d running, %d idle, %d tasks, %dMB', $this->total, $this->running, $this->idle, $this->totalTasks, $this->totalMemory);
    }
}
