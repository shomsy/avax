<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\PublicSurface;

use Avax\Components\Operations\Tasks\System\Capabilities\TaskBus;
use DateInterval;

final readonly class Tasks
{
    public static function dispatch(object $task) : void
    {
        $bus = new TaskBus();
        $bus->dispatch($task);
    }

    public static function later(object $task, DateInterval $delay) : void
    {
        $bus = new TaskBus();
        $bus->dispatchlater($task, $delay);
    }

    public static function batch(array $tasks) : TaskBatch
    {
        return new TaskBatch($tasks);
    }
}

final readonly class TaskBatch
{
    /** @var list<object> */
    private array $tasks;

    public function __construct(array $tasks)
    {
        $this->tasks = $tasks;
    }

    public function dispatch() : void
    {
        $bus = new TaskBus();

        foreach ($this->tasks as $task) {
            $bus->dispatch($task);
        }
    }
}