<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities;

use DateInterval;


final class TaskBus
{
    private array $handlers = [];

    private readonly SyncDriver $syncDriver;

    public function __construct()
    {
        $this->syncDriver = new SyncDriver();
    }

    public function register(string $taskClass, TaskHandlerInterface $taskHandler): void
    {
        $this->handlers[$taskClass] = $taskHandler;
    }

    public function dispatch(object $task): void
    {
        $class = $task::class;

        if (isset($this->handlers[$class])) {
            $this->handlers[$class]->handle($task);

            return;
        }

        $this->syncDriver->dispatch($task);
    }

    public function dispatchlater(object $task, DateInterval $dateInterval): void
    {
        $this->syncDriver->dispatchlater($task, $dateInterval);
    }
}

