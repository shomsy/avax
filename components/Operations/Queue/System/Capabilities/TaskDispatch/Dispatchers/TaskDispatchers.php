<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Dispatchers;

use DateInterval;

final class SyncDispatcher
{
    public function dispatch(object $task): void
    {
        $this->execute($task);
    }

    private function execute(object $task): void
    {
        if (method_exists($task, 'handle')) {
            $task->handle();
        } elseif (method_exists($task, '__invoke')) {
            $task();
        }
    }
}

final class AsyncDispatcher
{
    public function dispatch(object $task): void
    {
        $this->enqueue($task);
    }

    private function enqueue(object $task): void
    {
        $class = $task::class;
        echo "Queued task: {$class}\n";
    }
}

final class DeferredDispatcher
{
    private DateInterval $delay;

    public function __construct(DateInterval $delay)
    {
        $this->delay = $delay;
    }

    public function dispatch(object $task): void
    {
        $ms = (int) (($this->delay->i * 60 + $this->delay->s) * 1000);

        if ($ms === 0) {
            $this->executeNow($task);

            return;
        }

        $class = $task::class;
        echo "Scheduled task: {$class} in {$ms}ms\n";

        register_shutdown_function(fn () => $this->executeLater($task, $ms));
    }

    private function executeNow(object $task): void
    {
        (new SyncDispatcher)->dispatch($task);
    }

    private function executeLater(object $task, int $delayMs): void
    {
        usleep($delayMs * 1000);

        $this->executeNow($task);
    }
}
