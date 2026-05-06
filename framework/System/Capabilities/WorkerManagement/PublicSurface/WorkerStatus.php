<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\WorkerManagement\PublicSurface;

use Stringable;

final readonly class WorkerStatus implements Stringable
{
    public function __construct(
        public int $total,
        public int $running,
        public int $idle,
        public int $totalTasks,
        public int $totalMemory,
    ) {
    }

    /**
     * @return array{total: int, running: int, idle: int, total_tasks: int, total_memory_mb: int}
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'running' => $this->running,
            'idle' => $this->idle,
            'total_tasks' => $this->totalTasks,
            'total_memory_mb' => $this->totalMemory,
        ];
    }

    public function __toString(): string
    {
        return sprintf('Workers: %d total, %d running, %d idle, %d tasks, %dMB', $this->total, $this->running, $this->idle, $this->totalTasks, $this->totalMemory);
    }
}
