<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\Dispatchers;

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
