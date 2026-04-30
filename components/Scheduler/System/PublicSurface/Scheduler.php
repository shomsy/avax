<?php

declare(strict_types=1);

namespace Avax\Components\Scheduler\System\PublicSurface;

use Avax\Components\Scheduler\System\Capabilities\Cron\CronExpression;
use Avax\Components\Scheduler\System\Capabilities\TaskHistory\SchedulerHistory;
use Closure;

final class Scheduler
{
    private static TaskRunner $runner;
    private static array      $scheduledTasks = [];

    public static function schedule(string $expression, Closure $task) : ScheduledTask
    {
        self::$scheduledTasks[] = new ScheduledTask($expression, $task);

        return new ScheduledTask($expression, $task);
    }

    public static function scheduled() : array
    {
        return self::$scheduledTasks;
    }

    public static function register(string $cronExpression, callable $task) : ScheduledTask
    {
        return new ScheduledTask($cronExpression, $task);
    }

    public static function runDueTasks() : SchedulerReport
    {
        $runner = self::getRunner();

        return $runner->run();
    }

    private static function getRunner() : TaskRunner
    {
        if (! isset(self::$runner)) {
            self::$runner = new TaskRunner();
        }

        return self::$runner;
    }

    public static function history(int $limit = 100) : array
    {
        return SchedulerHistory::last($limit);
    }
}

final readonly class ScheduledTask
{
    public function __construct(
        public string   $expression,
        public Closure $task,
    ) {}

    public function isDue() : bool
    {
        return CronExpression::matches($this->expression);
    }
}

final readonly class SchedulerReport
{
    /** @var list<array{task: string, status: string, duration_ms: float}> */
    public array $executed;

    public function __construct(array $executed = [])
    {
        $this->executed = $executed;
    }

    public function addExecuted(string $task, string $status, float $duration) : void
    {
        $this->executed[] = [
            'task'        => $task,
            'status'      => $status,
            'duration_ms' => $duration,
        ];
    }

    public function count() : int
    {
        return count($this->executed);
    }
}

final readonly class TaskRunner
{
    /**
     * @return SchedulerReport
     */
    public function run() : SchedulerReport
    {
        $report = new SchedulerReport();

        // Implementacija zavisi od registered tasks
        // Ovde ide logic za pogonjenje scheduled taskova

        return $report;
    }
}