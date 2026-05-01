<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Scheduler\System\PublicSurface;

use Avax\Components\Operations\Scheduler\System\Capabilities\TaskHistory\SchedulerHistory;
use Throwable;

final readonly class TaskRunner
{
    public function run(): SchedulerReport
    {
        $report       = new SchedulerReport();

        foreach (Scheduler::scheduled() as $index => $scheduledTask) {
            if (! $scheduledTask instanceof ScheduledTask || ! $scheduledTask->isDue()) {
                continue;
            }

            $startedAt = microtime(true);
            $status   = 'ok';

            try {
                ($scheduledTask->task)();
            } catch (Throwable) {
                $status = 'failed';
            }

            $duration = (microtime(true) - $startedAt) * 1000;
            $taskName = 'task_' . $index;
            $report->addExecuted(task: $taskName, status: $status, duration: $duration);
            SchedulerHistory::record(task: $taskName, status: $status, durationMs: $duration);
        }

        return $report;
    }
}
