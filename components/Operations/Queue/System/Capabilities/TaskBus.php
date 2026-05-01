<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Tasks\System\Capabilities;

use DateInterval;

interface TaskHandlerInterface
{
    public function handle(object $task): void;
}

interface TaskDriverInterface
{
    public function dispatch(object $task): void;

    public function dispatchlater(object $task, DateInterval $delay): void;
}

final class TaskBus
{
    private array $handlers = [];

    private SyncDriver $driver;

    public function __construct()
    {
        $this->driver = new SyncDriver();
    }

    public function register(string $taskClass, TaskHandlerInterface $handler): void
    {
        $this->handlers[$taskClass] = $handler;
    }

    public function dispatch(object $task): void
    {
        $class = $task::class;

        if (isset($this->handlers[$class])) {
            $this->handlers[$class]->handle($task);

            return;
        }

        $this->driver->dispatch($task);
    }

    public function dispatchlater(object $task, DateInterval $delay): void
    {
        $this->driver->dispatchlater($task, $delay);
    }
}

final readonly class SyncDriver implements TaskDriverInterface
{
    public function dispatchlater(object $task, DateInterval $delay): void
    {
        $ms = (int) (($delay->i * 60 + $delay->s) * 1000);

        $this->schedule($task, $ms);
    }

    private function schedule(object $task, int $delayMs): void
    {
        usleep($delayMs * 1000);
        $this->dispatch($task);
    }

    public function dispatch(object $task): void
    {
        $class = $task::class;

        if (method_exists($task, '__invoke')) {
            ($task)();
        }
    }
}
