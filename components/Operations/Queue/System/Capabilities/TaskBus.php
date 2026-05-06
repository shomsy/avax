<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities;

use DateInterval;

interface TaskHandlerInterface
{
    public function handle(object $task): void;
}

interface TaskDriverInterface
{
    public function dispatch(object $task): void;

    public function dispatchlater(object $task, DateInterval $dateInterval): void;
}

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

final readonly class SyncDriver implements TaskDriverInterface
{
    public function dispatchlater(object $task, DateInterval $dateInterval): void
    {
        $ms = (int) (($dateInterval->i * 60 + $dateInterval->s) * 1000);

        $this->schedule($task, $ms);
    }

    private function schedule(object $task, int $delayMs): void
    {
        usleep($delayMs * 1000);
        $this->dispatch($task);
    }

    public function dispatch(object $task): void
    {
        if (method_exists($task, '__invoke')) {
            ($task)();
        }
    }
}
