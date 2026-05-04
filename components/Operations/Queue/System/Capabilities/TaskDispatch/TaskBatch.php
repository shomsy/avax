<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch;

use Closure;

final class TaskBatch
{
    private ?Closure $then = null;

    public function __construct(
        /** @var list<object> */
        private readonly array $tasks
    )
    {
    }

    public function then(Closure $callback): self
    {
        $this->then = $callback;

        return $this;
    }

    public function dispatch(): void
    {
        foreach ($this->tasks as $task) {
            TaskDispatch::dispatch($task);
        }

        if ($this->then instanceof Closure) {
            ($this->then)();
        }
    }
}
