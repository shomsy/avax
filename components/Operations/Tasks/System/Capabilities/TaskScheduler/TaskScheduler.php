<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Capabilities\TaskScheduler;

final class TaskScheduler
{
    /**
     * @var array<string, array{name: string, task: callable, cron: string, enabled: bool, lastRun: ?int}>
     */
    private array $tasks = [];

    public function schedule(string $id, string $name, callable $task, string $cronExpression) : self
    {
        $this->tasks[$id] = [
            'name'    => $name,
            'task'    => $task,
            'cron'    => $cronExpression,
            'enabled' => true,
            'lastRun' => null,
        ];

        return $this;
    }

    public function unschedule(string $id) : self
    {
        unset($this->tasks[$id]);

        return $this;
    }

    public function disable(string $id) : self
    {
        if (isset($this->tasks[$id])) {
            $this->tasks[$id]['enabled'] = false;
        }

        return $this;
    }

    public function enable(string $id) : self
    {
        if (isset($this->tasks[$id])) {
            $this->tasks[$id]['enabled'] = true;
        }

        return $this;
    }

    /**
     * @return array<string, array{name: string, task: callable, cron: string, enabled: bool, lastRun: ?int}>
     */
    public function tasks() : array
    {
        return $this->tasks;
    }

    public function find(string $id) : ?array
    {
        return $this->tasks[$id] ?? null;
    }
}
