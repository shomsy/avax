<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\WorkerManagement\Capabilities\Workers;

use Closure;
use RuntimeException;

final class WorkerProcess
{
    private int $pid = 0;

    private bool $running = false;

    private bool $idle = true;

    private int $taskCount = 0;

    private int $memoryUsage = 0;

    private int $maxMemory = 128;

    private int $startTime = 0;

    /**
     * @param  array{max_memory?: int}  $options
     */
    public function __construct(private readonly array $options = [])
    {
        $this->maxMemory = $this->options['max_memory'] ?? 128;
    }

    public function restart(bool $graceful): void
    {
        if ($graceful) {
            $this->drain();
            sleep(1);
        }

        $this->stop();
        $this->start();
    }

    public function drain(): void
    {
        $this->idle = true;
    }

    public function stop(): void
    {
        if ($this->pid > 0) {
            posix_kill($this->pid, SIGTERM);
        }

        $this->running = false;
    }

    public function start(): void
    {
        $pid = getmypid();

        if ($pid === false) {
            throw new RuntimeException('Unable to resolve current process id.');
        }

        $this->pid = $pid;
        $this->running = true;
        $this->startTime = time();
        $this->idle = true;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function isIdle(): bool
    {
        return $this->idle;
    }

    public function isDead(): bool
    {
        return ! $this->running;
    }

    public function taskCount(): int
    {
        return $this->taskCount;
    }

    public function memoryUsage(): int
    {
        return $this->memoryUsage;
    }

    public function maxMemory(): int
    {
        return $this->maxMemory;
    }

    public function uptime(): int
    {
        return time() - $this->startTime;
    }

    public function execute(Closure $task): void
    {
        $this->idle = false;

        try {
            $task();
            $this->taskCount++;
        } finally {
            $this->idle = true;
        }
    }
}
