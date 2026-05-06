<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\PublicSurface;

use Avax\Components\Operations\Tasks\System\Capabilities\TaskBus;
use DateInterval;

final readonly class Tasks
{
    public static function dispatch(object $task): void
    {
        $taskBus = new TaskBus();
        $taskBus->dispatch($task);
    }

    public static function later(object $task, DateInterval $dateInterval): void
    {
        $taskBus = new TaskBus();
        $taskBus->dispatchlater($task, $dateInterval);
    }

    public static function batch(array $tasks): TaskBatch
    {
        return new TaskBatch($tasks);
    }
}

final readonly class TaskBatch
{
    public function __construct(
        /** @var list<object> */
        private array $tasks
    ) {
    }

    public function dispatch(): void
    {
        $taskBus = new TaskBus();

        foreach ($this->tasks as $task) {
            $taskBus->dispatch($task);
        }
    }
}
