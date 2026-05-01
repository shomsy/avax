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
        echo sprintf('Queued task: %s%s', $class, PHP_EOL);
    }
}

final readonly class DeferredDispatcher
{
    public function __construct(private DateInterval $dateInterval)
    {
    }

    public function dispatch(object $task): void
    {
        $ms = (int) (($this->dateInterval->i * 60 + $this->dateInterval->s) * 1000);

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
        new SyncDispatcher()->dispatch($task);
    }

    private function executeLater(object $task, int $delayMs): void
    {
        usleep($delayMs * 1000);

        $this->executeNow($task);
    }
}
