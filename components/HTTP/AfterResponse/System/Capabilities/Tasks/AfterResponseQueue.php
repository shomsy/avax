<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\AfterResponse\System\Capabilities\Tasks;

use Closure;
use Throwable;

final class AfterResponseQueue
{
    /** @var list<AfterResponseTask> */
    private array $tasks = [];

    public function enqueue(AfterResponseTask $task): void
    {
        $this->tasks[] = $task;
    }

    public function execute(): void
    {
        while ($task = array_shift($this->tasks)) {
            try {
                $task->execute();
            } catch (Throwable $e) {
                error_log('AfterResponse error: ' . $e->getMessage());
            }
        }
    }

    public function isEmpty(): bool
    {
        return empty($this->tasks);
    }

    public function count(): int
    {
        return count($this->tasks);
    }
}

final readonly class AfterResponseTask
{
    private Closure $task;

    public function __construct(Closure $task)
    {
        $this->task = $task;
    }

    public function execute(): void
    {
        ($this->task)();
    }
}
