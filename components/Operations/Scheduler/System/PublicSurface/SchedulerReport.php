<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Scheduler\System\PublicSurface;

final class SchedulerReport
{
    public function __construct(
        /** @var list<array{task: string, status: string, duration_ms: float}> */
        public array $executed = []
    ) {
    }

    public function addExecuted(string $task, string $status, float $duration): void
    {
        $this->executed[] = [
            'task' => $task,
            'status' => $status,
            'duration_ms' => $duration,
        ];
    }

    public function count(): int
    {
        return count($this->executed);
    }
}
