<?php

declare(strict_types=1);

namespace Avax\Components\WorkerManager\System\PublicSurface;

use Avax\Components\WorkerManager\System\Capabilities\Lifecycle\GracefulShutdown;
use Avax\Components\WorkerManager\System\Capabilities\Workers\WorkerProcess;

final class WorkerManager
{
    private static WorkerPool $pool;

    public static function start(int $processes = 1, array $options = []) : void
    {
        self::pool()->start($processes, $options);
    }

    private static function pool() : WorkerPool
    {
        if (! isset(self::$pool)) {
            self::$pool = new WorkerPool();
        }

        return self::$pool;
    }

    public static function stop() : void
    {
        self::pool()->stop();
    }

    public static function restart(bool $graceful = true) : void
    {
        self::pool()->restart($graceful);
    }

    public static function status() : WorkerStatus
    {
        return self::pool()->status();
    }

    public static function drain() : void
    {
        self::pool()->drain();
    }
}

final class WorkerPool
{
    /** @var list<WorkerProcess> */
    private array $workers = [];
    private bool  $running = false;

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
        $running     = 0;
        $idle        = 0;
        $totalTasks  = 0;
        $totalMemory = 0;

        foreach ($this->workers as $worker) {
            if ($worker->isRunning()) {
                $running++;
            }

            if ($worker->isIdle()) {
                $idle++;
            }

            $totalTasks  += $worker->taskCount();
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

final readonly class WorkerStatus
{
    public function __construct(
        public int $total,
        public int $running,
        public int $idle,
        public int $totalTasks,
        public int $totalMemory,
    ) {}

    public function toArray() : array
    {
        return [
            'total'           => $this->total,
            'running'         => $this->running,
            'idle'            => $this->idle,
            'total_tasks'     => $this->totalTasks,
            'total_memory_mb' => $this->totalMemory,
        ];
    }

    public function __toString() : string
    {
        return "Workers: {$this->total} total, {$this->running} running, {$this->idle} idle, {$this->totalTasks} tasks, {$this->totalMemory}MB";
    }
}