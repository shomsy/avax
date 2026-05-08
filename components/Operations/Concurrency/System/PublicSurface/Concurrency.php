<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\PublicSurface;

use Avax\Components\Operations\Concurrency\System\Flows\RaceTasks\RaceTasks;
use Avax\Components\Operations\Concurrency\System\Flows\RunConcurrentTasks\RunConcurrentTasks;
use Avax\Components\Operations\Concurrency\System\Flows\StartTask\StartTask;
use Avax\Components\Operations\Concurrency\System\Flows\WaitForTask\WaitForTask;
use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentResult;
use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentTask;
use Closure;

final readonly class Concurrency
{
    /**
     * Run multiple tasks concurrently with optional concurrency limit.
     *
     * @param array<string|int, Closure(): mixed> $tasks
     */
    public static function run(array $tasks, int|null $maxConcurrent = null) : ConcurrentResult
    {
        return (new RunConcurrentTasks())->run(tasks: $tasks, maxConcurrent: $maxConcurrent);
    }

    /**
     * Run multiple tasks concurrently and wait for all.
     *
     * @param array<string|int, Closure(): mixed> $tasks
     */
    public static function all(array $tasks) : ConcurrentResult
    {
        return self::run(tasks: $tasks);
    }

    /**
     * Run multiple tasks and return the first successful result.
     *
     * @param list<Closure(): mixed> $tasks
     */
    public static function race(array $tasks) : mixed
    {
        return (new RaceTasks())->race(tasks: $tasks);
    }

    /**
     * Start a task without waiting for result.
     *
     * @template TResult
     * @param Closure(): TResult $task
     */
    public static function start(Closure $task) : ConcurrentTask
    {
        return (new StartTask())->start(task: $task);
    }

    /**
     * Await a previously started task.
     */
    public static function await(ConcurrentTask $task) : mixed
    {
        return (new WaitForTask())->await(task: $task);
    }
}
