<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\AfterResponse\System\Capabilities\Tasks;

use Closure;
use Throwable;

final class AfterResponseQueue
{
    /** @var list<AfterResponseTask> */
    private array $tasks = [];

    public function enqueue(AfterResponseTask $afterResponseTask) : void
    {
        $this->tasks[] = $afterResponseTask;
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
        return $this->tasks === [];
    }

    public function count(): int
    {
        return count($this->tasks);
    }
}

final readonly class AfterResponseTask
{
    public function __construct(private Closure $task)
    {
    }

    public function execute(): void
    {
        ($this->task)();
    }
}
