<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\Dispatchers;

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
