<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Capabilities\TaskQueue;

final class TaskQueue
{
    /**
     * @var list<array{id: string, task: callable, priority: int, createdAt: int}>
     */
    private array $tasks = [];

    public function push(callable $task, int $priority = 0) : string
    {
        $id = bin2hex(random_bytes(4));

        $this->tasks[] = [
            'id'        => $id,
            'task'      => $task,
            'priority'  => $priority,
            'createdAt' => time(),
        ];

        $this->sort();

        return $id;
    }

    private function sort() : void
    {
        usort($this->tasks, fn (array $a, array $b) : int => $b['priority'] <=> $a['priority']);
    }

    public function pop() : callable|null
    {
        $item = array_shift($this->tasks);

        return $item !== null ? $item['task'] : null;
    }

    public function size() : int
    {
        return count($this->tasks);
    }

    public function isEmpty() : bool
    {
        return $this->tasks === [];
    }

    public function clear() : void
    {
        $this->tasks = [];
    }
}
