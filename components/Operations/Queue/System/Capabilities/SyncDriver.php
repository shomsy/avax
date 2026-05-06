<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities;

use DateInterval;

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
