<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\PublicSurface;

use Avax\Components\Operations\Queue\System\Capabilities\TaskBus;

final readonly class TaskBatch
{
    public function __construct(
        /** @var list<object> */
        private array $tasks
    )
    {
    }

    public function dispatch(): void
    {
        $taskBus = new TaskBus();

        foreach ($this->tasks as $task) {
            $taskBus->dispatch($task);
        }
    }
}
