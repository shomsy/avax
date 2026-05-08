<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Concurrency\System\Capabilities\TrackRunningTasks;

use Avax\Components\Operations\Concurrency\System\Foundation\ConcurrentTask;
use Avax\Components\Operations\Concurrency\System\Foundation\TaskId;

final class TrackRunningTasks
{
    /** @var array<string|int, ConcurrentTask> */
    private array $tasks = [];

    public function track(ConcurrentTask $task) : void
    {
        $this->tasks[$task->id->value] = $task;
    }

    public function forget(TaskId|string|int $id) : void
    {
        $key = $id instanceof TaskId ? $id->value : $id;
        unset($this->tasks[$key]);
    }

    public function get(TaskId|string|int $id) : ?ConcurrentTask
    {
        $key = $id instanceof TaskId ? $id->value : $id;

        return $this->tasks[$key] ?? null;
    }

    /**
     * @return list<ConcurrentTask>
     */
    public function all() : array
    {
        return array_values($this->tasks);
    }

    public function count() : int
    {
        return count($this->tasks);
    }

    public function clear() : void
    {
        $this->tasks = [];
    }
}
