<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Scheduler\System\PublicSurface;

use Avax\Components\Operations\Scheduler\System\Capabilities\TaskHistory\SchedulerHistory;
use Closure;

final class Scheduler
{
    private static TaskRunner $runner;

    private static array $scheduledTasks = [];

    public static function schedule(string $expression, Closure $task): ScheduledTask
    {
        $scheduledTask = new ScheduledTask($expression, $task);
        self::$scheduledTasks[] = $scheduledTask;

        return $scheduledTask;
    }

    public static function scheduled(): array
    {
        return self::$scheduledTasks;
    }

    public static function register(string $cronExpression, callable $task): ScheduledTask
    {
        return new ScheduledTask($cronExpression, $task);
    }

    public static function runDueTasks(): SchedulerReport
    {
        $runner = self::getRunner();

        return $runner->run();
    }

    private static function getRunner(): TaskRunner
    {
        if (! isset(self::$runner)) {
            self::$runner = new TaskRunner;
        }

        return self::$runner;
    }

    public static function history(int $limit = 100): array
    {
        return SchedulerHistory::last($limit);
    }

    public static function clear(): void
    {
        self::$scheduledTasks = [];
        SchedulerHistory::clear();
    }
}
