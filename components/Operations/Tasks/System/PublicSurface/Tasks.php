<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\PublicSurface;

use Avax\Components\Operations\Tasks\System\Capabilities\TaskQueue\TaskQueue;
use Avax\Components\Operations\Tasks\System\Capabilities\TaskRetry\TaskRetryPolicy;
use Avax\Components\Operations\Tasks\System\Capabilities\TaskRunner\TaskRunner;
use Avax\Components\Operations\Tasks\System\Capabilities\TaskScheduler\TaskScheduler;
use Avax\Components\Operations\Tasks\System\Flows\CancelTask\CancelTask;
use Avax\Components\Operations\Tasks\System\Flows\ExecuteTask\ExecuteTask;
use Avax\Components\Operations\Tasks\System\Flows\RetryTask\RetryTask;
use Avax\Components\Operations\Tasks\System\Flows\ScheduleTask\ScheduleTask;

final readonly class Tasks
{
    public static function runner() : TaskRunner
    {
        return new TaskRunner();
    }

    public static function queue() : TaskQueue
    {
        return new TaskQueue();
    }

    public static function scheduler() : TaskScheduler
    {
        return new TaskScheduler();
    }

    /**
     * @return array{id: string, status: string, result: mixed, error: string|null, duration: int|null}
     */
    public static function execute(TaskRunner $runner, callable $task) : array
    {
        return (new ExecuteTask())->execute($runner, $task);
    }

    public static function schedule(TaskScheduler $scheduler, string $name, callable $task, string $cronExpression) : void
    {
        (new ScheduleTask())->schedule($scheduler, $name, $task, $cronExpression);
    }

    /**
     * @return array{success: bool, attempts: int, result: array<string, mixed>|null, error: string|null}
     */
    public static function retryTask(TaskRunner $runner, callable $task, TaskRetryPolicy $policy) : array
    {
        return (new RetryTask())->retry($runner, $task, $policy);
    }

    public static function retry(?TaskRetryPolicy $policy = null) : TaskRetryPolicy
    {
        return $policy ?? new TaskRetryPolicy();
    }

    public static function cancel(TaskScheduler $scheduler, string $taskId) : bool
    {
        return (new CancelTask())->cancel($scheduler, $taskId);
    }
}
